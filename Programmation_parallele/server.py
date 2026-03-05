import socket
import threading
import json
from datetime import datetime

class ChatServer:
    def __init__(self, host='0.0.0.0', port=5555):
        self.host = host
        self.port = port
        self.server_socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        self.server_socket.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
        self.clients = {}  # {username: {'socket': socket, 'address': address}}
        
    def start(self):
        try:
            self.server_socket.bind((self.host, self.port))
            self.server_socket.listen(5)
            print(f"[SERVEUR] Démarré sur {self.host}:{self.port}")
            print(f"[SERVEUR] Adresse IP: {self.get_local_ip()}")
            print("[SERVEUR] En attente de connexions...")
            
            while True:
                client_socket, address = self.server_socket.accept()
                print(f"[CONNEXION] Nouvelle tentative de connexion de {address}")
                
                thread = threading.Thread(target=self.handle_client, args=(client_socket, address))
                thread.daemon = True
                thread.start()
        except KeyboardInterrupt:
            print("\n[SERVEUR] Arrêt demandé...")
        finally:
            self.shutdown()
    
    def get_local_ip(self):
        """Récupère l'adresse IP locale du serveur"""
        try:
            s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
            s.connect(('8.8.8.8', 80))
            ip = s.getsockname()[0]
            s.close()
            return ip
        except:
            return "127.0.0.1"
    
    def broadcast_user_list(self):
        """Envoie la liste des utilisateurs à tous les clients connectés"""
        users = list(self.clients.keys())
        
        message = {
            'type': 'user_list',
            'users': users
        }
        
        print(f"[BROADCAST] Envoi liste utilisateurs: {users}")
        
        disconnected = []
        for username, client_data in self.clients.items():
            try:
                client_data['socket'].send(json.dumps(message).encode('utf-8'))
            except Exception as e:
                print(f"[ERREUR] Envoi liste à {username}: {e}")
                disconnected.append(username)
        
        # Nettoyer les clients déconnectés
        for username in disconnected:
            self.remove_client(username)
    
    def broadcast_group_message(self, sender_username, content, timestamp):
        """Envoie un message à tous les clients connectés"""
        message = {
            'type': 'group',
            'from': sender_username,
            'content': content,
            'timestamp': timestamp
        }
        
        print(f"[GROUPE] {sender_username} -> TOUS: {content}")
        
        disconnected = []
        for username, client_data in self.clients.items():
            try:
                client_data['socket'].send(json.dumps(message).encode('utf-8'))
            except Exception as e:
                print(f"[ERREUR] Envoi groupe à {username}: {e}")
                disconnected.append(username)
        
        # Nettoyer les clients déconnectés
        for username in disconnected:
            self.remove_client(username)
        
        # Confirmation à l'expéditeur
        if sender_username in self.clients:
            confirm_msg = {
                'type': 'group_sent',
                'content': content,
                'timestamp': timestamp
            }
            try:
                self.clients[sender_username]['socket'].send(json.dumps(confirm_msg).encode('utf-8'))
            except:
                pass
    
    def remove_client(self, username):
        """Retire un client déconnecté"""
        if username in self.clients:
            print(f"[DÉCONNEXION] {username} s'est déconnecté")
            
            # Fermer la socket
            try:
                self.clients[username]['socket'].close()
            except:
                pass
            
            # Supprimer le client
            del self.clients[username]
            
            # Informer les autres clients
            self.broadcast_user_list()
    
    def handle_client(self, client_socket, address):
        username = None
        try:
            # Recevoir le nom d'utilisateur
            data = client_socket.recv(1024).decode('utf-8')
            username_data = json.loads(data)
            username = username_data['username']
            
            print(f"[TENTATIVE] {address} essaie de se connecter comme '{username}'")
            
            # Vérifier si le nom d'utilisateur est déjà pris
            if username in self.clients:
                error_msg = {
                    'type': 'error',
                    'message': 'Ce nom d\'utilisateur est déjà utilisé'
                }
                client_socket.send(json.dumps(error_msg).encode('utf-8'))
                client_socket.close()
                print(f"[REFUS] Nom d'utilisateur '{username}' déjà pris")
                return
            
            # Enregistrer le client
            self.clients[username] = {
                'socket': client_socket,
                'address': address
            }
            
            print(f"[ACCEPTÉ] {username} est connecté depuis {address}")
            
            # Envoyer message de bienvenue
            welcome_msg = {
                'type': 'welcome',
                'message': f'Bienvenue {username} sur le chat!',
                'username': username
            }
            client_socket.send(json.dumps(welcome_msg).encode('utf-8'))
            
            # Envoyer la liste des utilisateurs à tout le monde
            self.broadcast_user_list()
            
            # Gérer les messages entrants
            while True:
                try:
                    data = client_socket.recv(4096).decode('utf-8')
                    if not data:
                        print(f"[DÉCONNEXION] {username} a fermé la connexion")
                        break
                    
                    message = json.loads(data)
                    print(f"[MESSAGE] De {username}: {message.get('type', 'inconnu')}")
                    self.process_message(username, message)
                    
                except json.JSONDecodeError as e:
                    print(f"[ERREUR] JSON invalide de {username}: {e}")
                    continue
                except socket.error as e:
                    print(f"[ERREUR] Socket {username}: {e}")
                    break
                except Exception as e:
                    print(f"[ERREUR] Réception message de {username}: {e}")
                    break
                    
        except Exception as e:
            print(f"[ERREUR] Client {address}: {e}")
        finally:
            if username:
                self.remove_client(username)
            client_socket.close()
    
    def process_message(self, sender_username, message):
        """Traite les différents types de messages"""
        msg_type = message.get('type')
        
        if msg_type == 'private':
            self.handle_private_message(sender_username, message)
        elif msg_type == 'group':
            self.handle_group_message(sender_username, message)
    
    def handle_private_message(self, sender_username, message):
        """Gère les messages privés"""
        recipient_username = message.get('recipient')
        content = message.get('content')
        timestamp = datetime.now().strftime('%H:%M')
        
        print(f"[PRIVÉ] {sender_username} -> {recipient_username}: {content}")
        
        if recipient_username in self.clients:
            # Envoyer au destinataire
            private_msg = {
                'type': 'private',
                'from': sender_username,
                'content': content,
                'timestamp': timestamp
            }
            
            try:
                recipient_socket = self.clients[recipient_username]['socket']
                recipient_socket.send(json.dumps(private_msg).encode('utf-8'))
                print(f"[ENVOYÉ] Message à {recipient_username}")
                
                # Confirmation à l'expéditeur
                confirm_msg = {
                    'type': 'private_sent',
                    'to': recipient_username,
                    'content': content,
                    'timestamp': timestamp
                }
                
                sender_socket = self.clients[sender_username]['socket']
                sender_socket.send(json.dumps(confirm_msg).encode('utf-8'))
                
            except Exception as e:
                print(f"[ERREUR] Envoi à {recipient_username}: {e}")
                self.remove_client(recipient_username)
        else:
            print(f"[ERREUR] Destinataire {recipient_username} non trouvé")
            
            # Informer l'expéditeur que le destinataire n'est pas connecté
            error_msg = {
                'type': 'error',
                'message': f"L'utilisateur {recipient_username} n'est pas connecté"
            }
            try:
                sender_socket = self.clients[sender_username]['socket']
                sender_socket.send(json.dumps(error_msg).encode('utf-8'))
            except:
                pass
    
    def handle_group_message(self, sender_username, message):
        """Gère les messages de groupe"""
        content = message.get('content')
        timestamp = datetime.now().strftime('%H:%M')
        
        self.broadcast_group_message(sender_username, content, timestamp)
    
    def shutdown(self):
        """Arrêt propre du serveur"""
        print("[SERVEUR] Arrêt en cours...")
        
        # Déconnecter tous les clients
        for username in list(self.clients.keys()):
            try:
                self.clients[username]['socket'].close()
            except:
                pass
        
        self.clients.clear()
        self.server_socket.close()
        print("[SERVEUR] Arrêté")

if __name__ == "__main__":
    server = ChatServer()
    server.start()