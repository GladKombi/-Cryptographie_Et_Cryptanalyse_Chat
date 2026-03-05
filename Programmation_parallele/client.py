import socket
import threading
import json
import tkinter as tk
from tkinter import messagebox
from datetime import datetime

class ChatClient:
    def __init__(self):
        self.socket = None
        self.connected = False
        self.username = ""
        self.users = []  # Liste des utilisateurs connectés
        self.current_chat = None  # "groupe" ou nom d'utilisateur
        self.messages = {}  # {username: [messages]} et aussi 'groupe': [messages]
        self.unread_counts = {}  # {username: nombre_messages_non_lus}
        
        self.setup_gui()
        
    def setup_gui(self):
        """Configuration de l'interface graphique"""
        self.root = tk.Tk()
        self.root.title("Chat Application")
        self.root.geometry("1000x600")
        self.root.minsize(800, 500)
        
        # Configuration des couleurs Telegram-like
        self.colors = {
            'bg': '#0f0f0f',
            'sidebar': '#1e1e1e',
            'chat_bg': '#262626',
            'message_bg_me': '#2b5278',
            'message_bg_other': '#2d2d2d',
            'message_bg_group': '#3a3a3a',
            'text': '#ffffff',
            'text_secondary': '#a0a0a0',
            'hover': '#2d2d2d',
            'border': '#333333',
            'unread_badge': '#2b5278',
            'unread_text': '#ffffff',
            'selected': '#2b5278',
            'group_color': '#4a6fa5'
        }
        
        self.root.configure(bg=self.colors['bg'])
        
        # Frame de connexion
        self.setup_login_frame()
        
        # Frame principal du chat
        self.setup_main_chat_frame()
        
        # Gestionnaire de fermeture de fenêtre
        self.root.protocol("WM_DELETE_WINDOW", self.on_closing)
        
    def setup_login_frame(self):
        """Frame de connexion avec configuration réseau"""
        self.login_frame = tk.Frame(self.root, bg=self.colors['bg'])
        self.login_frame.place(relx=0.5, rely=0.5, anchor='center')
        
        title = tk.Label(self.login_frame, text="Chat Application", 
                        font=('Helvetica', 24, 'bold'), 
                        fg='#2b5278', bg=self.colors['bg'])
        title.pack(pady=(0, 20))
        
        form_frame = tk.Frame(self.login_frame, bg=self.colors['sidebar'], 
                            padx=40, pady=40)
        form_frame.pack()
        
        # Configuration serveur
        tk.Label(form_frame, text="Configuration serveur", 
                font=('Helvetica', 14, 'bold'), 
                fg=self.colors['text'], bg=self.colors['sidebar']).pack(anchor='w', pady=(0, 15))
        
        # Adresse IP
        ip_frame = tk.Frame(form_frame, bg=self.colors['sidebar'])
        ip_frame.pack(fill='x', pady=(0, 10))
        
        tk.Label(ip_frame, text="Adresse IP:", 
                font=('Helvetica', 11), 
                fg=self.colors['text_secondary'], 
                bg=self.colors['sidebar']).pack(side='left', padx=(0, 10))
        
        self.server_ip = tk.Entry(ip_frame, font=('Helvetica', 11), 
                                width=20, bg='#3a3a3a', fg='white',
                                insertbackground='white', relief='flat')
        self.server_ip.pack(side='left')
        self.server_ip.insert(0, '127.0.0.1')
        
        # Port
        port_frame = tk.Frame(form_frame, bg=self.colors['sidebar'])
        port_frame.pack(fill='x', pady=(0, 20))
        
        tk.Label(port_frame, text="Port:", 
                font=('Helvetica', 11), 
                fg=self.colors['text_secondary'], 
                bg=self.colors['sidebar']).pack(side='left', padx=(0, 10))
        
        self.server_port = tk.Entry(port_frame, font=('Helvetica', 11), 
                                  width=10, bg='#3a3a3a', fg='white',
                                  insertbackground='white', relief='flat')
        self.server_port.pack(side='left')
        self.server_port.insert(0, '5555')
        
        # Séparateur
        separator = tk.Frame(form_frame, height=1, bg=self.colors['border'])
        separator.pack(fill='x', pady=20)
        
        # Configuration utilisateur
        tk.Label(form_frame, text="Configuration utilisateur", 
                font=('Helvetica', 14, 'bold'), 
                fg=self.colors['text'], bg=self.colors['sidebar']).pack(anchor='w', pady=(0, 15))
        
        tk.Label(form_frame, text="Nom d'utilisateur", 
                font=('Helvetica', 11), 
                fg=self.colors['text_secondary'], 
                bg=self.colors['sidebar']).pack(anchor='w')
        
        self.username_entry = tk.Entry(form_frame, font=('Helvetica', 12), 
                                     width=30, bg='#3a3a3a', fg='white',
                                     insertbackground='white', relief='flat')
        self.username_entry.pack(pady=(5, 20))
        self.username_entry.bind('<Return>', lambda e: self.connect_to_server())
        
        self.connect_btn = tk.Button(form_frame, text="Se connecter", 
                                   font=('Helvetica', 12, 'bold'),
                                   bg='#2b5278', fg='white', 
                                   activebackground='#1e3f5c',
                                   activeforeground='white',
                                   relief='flat', padx=20, pady=10,
                                   command=self.connect_to_server)
        self.connect_btn.pack()
        
        self.status_label = tk.Label(self.login_frame, text="", 
                                   font=('Helvetica', 10),
                                   fg=self.colors['text_secondary'], 
                                   bg=self.colors['bg'])
        self.status_label.pack(pady=(20, 0))
        
    def setup_main_chat_frame(self):
        """Interface principale du chat"""
        self.main_frame = tk.Frame(self.root, bg=self.colors['bg'])
        
        # Sidebar gauche (liste des contacts)
        self.sidebar = tk.Frame(self.main_frame, bg=self.colors['sidebar'], 
                              width=280)
        self.sidebar.pack(side='left', fill='y')
        self.sidebar.pack_propagate(False)
        
        # Header du sidebar avec profil
        self.setup_sidebar_header()
        
        # Recherche de contacts
        self.setup_search_bar()
        
        # Label "CONTACTS"
        contacts_label = tk.Label(self.sidebar, text="CONTACTS", 
                                font=('Helvetica', 11, 'bold'),
                                fg=self.colors['text_secondary'], 
                                bg=self.colors['sidebar'])
        contacts_label.pack(anchor='w', padx=15, pady=(10, 5))
        
        # Canvas pour les contacts (scrollable)
        self.contacts_canvas = tk.Canvas(self.sidebar, bg=self.colors['sidebar'],
                                       highlightthickness=0, height=400)
        self.contacts_canvas.pack(side='left', fill='both', expand=True, padx=(15, 5))
        
        contacts_scrollbar = tk.Scrollbar(self.sidebar, orient='vertical',
                                        command=self.contacts_canvas.yview)
        contacts_scrollbar.pack(side='right', fill='y', padx=(0, 5))
        
        # Frame pour les contacts (à l'intérieur du canvas)
        self.contacts_frame = tk.Frame(self.contacts_canvas, bg=self.colors['sidebar'])
        self.contacts_canvas.configure(yscrollcommand=contacts_scrollbar.set)
        
        self.contacts_canvas.create_window((0, 0), window=self.contacts_frame, 
                                         anchor='nw', width=250)
        
        self.contacts_frame.bind('<Configure>', 
                               lambda e: self.contacts_canvas.configure(
                                   scrollregion=self.contacts_canvas.bbox('all')
                               ))
        
        # Frame du chat principal
        self.setup_chat_area()
        
    def setup_sidebar_header(self):
        """En-tête du sidebar avec profil utilisateur"""
        header_frame = tk.Frame(self.sidebar, bg=self.colors['sidebar'], height=80)
        header_frame.pack(fill='x', padx=15, pady=(20, 10))
        header_frame.pack_propagate(False)
        
        # Avatar utilisateur
        avatar_frame = tk.Frame(header_frame, width=50, height=50, bg='#2b5278')
        avatar_frame.pack(side='left', padx=(0, 10))
        avatar_frame.pack_propagate(False)
        
        self.avatar_label = tk.Label(avatar_frame, text="",
                                   font=('Helvetica', 20, 'bold'),
                                   fg='white', bg='#2b5278')
        self.avatar_label.pack(expand=True)
        
        # Informations utilisateur
        info_frame = tk.Frame(header_frame, bg=self.colors['sidebar'])
        info_frame.pack(side='left', fill='both', expand=True)
        
        self.username_label = tk.Label(info_frame, 
                                     font=('Helvetica', 16, 'bold'),
                                     fg=self.colors['text'], 
                                     bg=self.colors['sidebar'])
        self.username_label.pack(anchor='w')
        
        status_label = tk.Label(info_frame, text="● En ligne", 
                              font=('Helvetica', 11),
                              fg='#4CAF50', bg=self.colors['sidebar'])
        status_label.pack(anchor='w', pady=(5, 0))
        
        # Bouton déconnexion
        logout_btn = tk.Button(self.sidebar, text="Déconnexion", 
                             font=('Helvetica', 11),
                             bg='#3a3a3a', fg=self.colors['text'],
                             activebackground='#4a4a4a',
                             relief='flat', command=self.logout)
        logout_btn.pack(padx=15, pady=(0, 15), fill='x')
        
    def setup_search_bar(self):
        """Barre de recherche de contacts"""
        search_frame = tk.Frame(self.sidebar, bg=self.colors['sidebar'], height=40)
        search_frame.pack(fill='x', padx=15, pady=(0, 10))
        search_frame.pack_propagate(False)
        
        search_entry = tk.Entry(search_frame, 
                              font=('Helvetica', 11),
                              bg='#3a3a3a', fg=self.colors['text'],
                              insertbackground='white',
                              relief='flat')
        search_entry.pack(fill='both', expand=True)
        search_entry.insert(0, "🔍 Rechercher...")
        
        def on_focus_in(e):
            if search_entry.get() == "🔍 Rechercher...":
                search_entry.delete(0, 'end')
        
        def on_focus_out(e):
            if not search_entry.get():
                search_entry.insert(0, "🔍 Rechercher...")
        
        search_entry.bind('<FocusIn>', on_focus_in)
        search_entry.bind('<FocusOut>', on_focus_out)
        search_entry.bind('<KeyRelease>', self.filter_contacts)
        
        self.search_entry = search_entry
        
    def setup_chat_area(self):
        """Zone de chat principale"""
        self.chat_frame = tk.Frame(self.main_frame, bg=self.colors['chat_bg'])
        self.chat_frame.pack(side='right', fill='both', expand=True)
        
        # Header du chat
        self.chat_header = tk.Frame(self.chat_frame, bg=self.colors['sidebar'],
                                  height=80)
        self.chat_header.pack(fill='x')
        self.chat_header.pack_propagate(False)
        
        # Avatar du contact dans le header
        header_content = tk.Frame(self.chat_header, bg=self.colors['sidebar'])
        header_content.pack(anchor='w', padx=20, pady=(15, 5))
        
        self.chat_avatar_frame = tk.Frame(header_content, width=50, height=50,
                                        bg='#2b5278')
        self.chat_avatar_frame.pack(side='left', padx=(0, 15))
        self.chat_avatar_frame.pack_propagate(False)
        
        self.chat_avatar_label = tk.Label(self.chat_avatar_frame, text="",
                                        font=('Helvetica', 20, 'bold'),
                                        fg='white', bg='#2b5278')
        self.chat_avatar_label.pack(expand=True)
        
        # Infos contact
        chat_info_frame = tk.Frame(header_content, bg=self.colors['sidebar'])
        chat_info_frame.pack(side='left')
        
        self.chat_with_label = tk.Label(chat_info_frame, 
                                      text="Sélectionnez un contact", 
                                      font=('Helvetica', 16, 'bold'),
                                      fg=self.colors['text'], 
                                      bg=self.colors['sidebar'])
        self.chat_with_label.pack(anchor='w')
        
        self.chat_status_label = tk.Label(chat_info_frame, 
                                        text="", 
                                        font=('Helvetica', 11),
                                        fg=self.colors['text_secondary'], 
                                        bg=self.colors['sidebar'])
        self.chat_status_label.pack(anchor='w', pady=(2, 0))
        
        separator = tk.Frame(self.chat_frame, height=1, bg=self.colors['border'])
        separator.pack(fill='x')
        
        # Zone des messages
        self.messages_frame = tk.Frame(self.chat_frame, bg=self.colors['chat_bg'])
        self.messages_frame.pack(fill='both', expand=True, padx=20, pady=20)
        
        # Canvas pour le scroll des messages
        self.messages_canvas = tk.Canvas(self.messages_frame, 
                                       bg=self.colors['chat_bg'],
                                       highlightthickness=0)
        self.messages_canvas.pack(side='left', fill='both', expand=True)
        
        messages_scrollbar = tk.Scrollbar(self.messages_frame, orient='vertical',
                                        command=self.messages_canvas.yview)
        messages_scrollbar.pack(side='right', fill='y')
        
        self.messages_canvas.configure(yscrollcommand=messages_scrollbar.set)
        
        # Frame pour les messages (à l'intérieur du canvas)
        self.messages_container = tk.Frame(self.messages_canvas, bg=self.colors['chat_bg'])
        self.messages_canvas.create_window((0, 0), window=self.messages_container, 
                                         anchor='nw', width=self.messages_canvas.winfo_width())
        
        self.messages_container.bind('<Configure>', 
                                   lambda e: self.messages_canvas.configure(
                                       scrollregion=self.messages_canvas.bbox('all')
                                   ))
        
        self.messages_canvas.bind('<Configure>',
                                lambda e: self.messages_canvas.itemconfig(
                                    self.messages_canvas.find_all()[0], 
                                    width=e.width
                                ))
        
        # Frame pour l'envoi de messages
        self.input_frame = tk.Frame(self.chat_frame, bg=self.colors['sidebar'],
                                  height=90)
        self.input_frame.pack(fill='x', side='bottom')
        self.input_frame.pack_propagate(False)
        
        # Bouton pour chat de groupe
        self.group_btn = tk.Button(self.input_frame, text="👥 Groupe", 
                                 font=('Helvetica', 11),
                                 bg='#4a6fa5', fg='white',
                                 activebackground='#3a5a8c',
                                 relief='flat', width=10,
                                 command=self.select_group_chat)
        self.group_btn.pack(side='left', padx=(20, 10), pady=20)
        
        self.message_entry = tk.Text(self.input_frame, 
                                   font=('Helvetica', 12),
                                   height=3,
                                   bg='#3a3a3a', fg='white',
                                   insertbackground='white',
                                   relief='flat', padx=10, pady=10)
        self.message_entry.pack(side='left', fill='both', expand=True, 
                              padx=(0, 10), pady=20)
        self.message_entry.bind('<Return>', self.send_message_event)
        self.message_entry.bind('<Shift-Return>', lambda e: None)
        
        self.send_btn = tk.Button(self.input_frame, text="➤", 
                                font=('Helvetica', 16),
                                bg='#2b5278', fg='white',
                                activebackground='#1e3f5c',
                                activeforeground='white',
                                relief='flat', width=3,
                                command=self.send_message)
        self.send_btn.pack(side='right', padx=(0, 20), pady=20)
        
        # Désactiver la zone de message au début
        self.message_entry.config(state='disabled')
        self.send_btn.config(state='disabled')
        
    def filter_contacts(self, event=None):
        """Filtre les contacts selon la recherche"""
        search_term = self.search_entry.get().lower()
        
        for widget in self.contacts_frame.winfo_children():
            widget.destroy()
        
        # Ajouter le chat de groupe en premier
        self.create_group_chat_widget()
        
        # Ajouter les contacts
        for username in sorted(self.users):
            if username != self.username:
                if search_term in ["🔍 rechercher...", ""] or search_term in username.lower():
                    self.create_contact_widget(username)
    
    def create_group_chat_widget(self):
        """Crée un widget pour le chat de groupe"""
        contact_frame = tk.Frame(self.contacts_frame, bg=self.colors['sidebar'],
                               height=70)
        contact_frame.pack(fill='x', pady=(0, 5))
        contact_frame.pack_propagate(False)
        
        # Mettre en surbrillance si c'est le groupe sélectionné
        if self.current_chat == "groupe":
            contact_frame.config(bg=self.colors['selected'])
        
        # Avatar du groupe
        avatar_frame = tk.Frame(contact_frame, width=50, height=50,
                              bg=self.colors['group_color'])
        avatar_frame.pack(side='left', padx=(0, 10))
        avatar_frame.pack_propagate(False)
        
        avatar_label = tk.Label(avatar_frame, text="👥",
                              font=('Helvetica', 20),
                              fg='white', bg=self.colors['group_color'])
        avatar_label.pack(expand=True)
        
        # Informations
        info_frame = tk.Frame(contact_frame, bg=contact_frame['bg'])
        info_frame.pack(side='left', fill='both', expand=True)
        
        # Nom et badge non lus
        name_badge_frame = tk.Frame(info_frame, bg=contact_frame['bg'])
        name_badge_frame.pack(fill='x')
        
        name_label = tk.Label(name_badge_frame, text="Chat général",
                            font=('Helvetica', 12, 'bold'),
                            fg=self.colors['text'], bg=contact_frame['bg'])
        name_label.pack(side='left')
        
        # Badge de messages non lus pour le groupe
        unread_count = self.unread_counts.get('groupe', 0)
        if unread_count > 0:
            badge_frame = tk.Frame(name_badge_frame, 
                                 bg=self.colors['unread_badge'],
                                 padx=6, pady=2)
            badge_frame.pack(side='right', padx=(5, 0))
            
            badge_label = tk.Label(badge_frame, 
                                 text=str(unread_count),
                                 font=('Helvetica', 10, 'bold'),
                                 fg=self.colors['unread_text'],
                                 bg=self.colors['unread_badge'])
            badge_label.pack()
        
        # Dernier message
        last_msg = self.get_last_message('groupe')
        if last_msg:
            last_msg_label = tk.Label(info_frame, 
                                    text=last_msg[:30] + ('...' if len(last_msg) > 30 else ''),
                                    font=('Helvetica', 10),
                                    fg=self.colors['text_secondary'],
                                    bg=contact_frame['bg'])
            last_msg_label.pack(anchor='w', pady=(2, 0))
        
        # Nombre de participants
        participants_label = tk.Label(info_frame, 
                                    text=f"{len(self.users)} participants",
                                    font=('Helvetica', 10),
                                    fg=self.colors['text_secondary'],
                                    bg=contact_frame['bg'])
        participants_label.pack(anchor='w', pady=(2, 0))
        
        # Rendre cliquable
        def select_group(e):
            self.select_group_chat()
            # Réinitialiser la couleur de fond
            for widget in self.contacts_frame.winfo_children():
                widget.config(bg=self.colors['sidebar'])
            contact_frame.config(bg=self.colors['selected'])
        
        for widget in [contact_frame, avatar_frame, avatar_label, 
                      info_frame, name_label]:
            widget.bind('<Button-1>', select_group)
            widget.bind('<Enter>', lambda e, f=contact_frame: f.config(bg=self.colors['hover']) 
                       if self.current_chat != "groupe" else None)
            widget.bind('<Leave>', lambda e, f=contact_frame: f.config(bg=self.colors['selected']) 
                       if self.current_chat == "groupe" else f.config(bg=self.colors['sidebar']))
    
    def create_contact_widget(self, username):
        """Crée un widget de contact avec badge de messages non lus"""
        contact_frame = tk.Frame(self.contacts_frame, bg=self.colors['sidebar'],
                               height=70)
        contact_frame.pack(fill='x', pady=(0, 5))
        contact_frame.pack_propagate(False)
        
        # Mettre en surbrillance si c'est le contact sélectionné
        if username == self.current_chat:
            contact_frame.config(bg=self.colors['selected'])
        
        # Avatar
        avatar_frame = tk.Frame(contact_frame, width=50, height=50,
                              bg='#2b5278')
        avatar_frame.pack(side='left', padx=(0, 10))
        avatar_frame.pack_propagate(False)
        
        avatar_label = tk.Label(avatar_frame, text=username[0].upper(),
                              font=('Helvetica', 18, 'bold'),
                              fg='white', bg='#2b5278')
        avatar_label.pack(expand=True)
        
        # Informations
        info_frame = tk.Frame(contact_frame, bg=contact_frame['bg'])
        info_frame.pack(side='left', fill='both', expand=True)
        
        # Nom et badge non lus
        name_badge_frame = tk.Frame(info_frame, bg=contact_frame['bg'])
        name_badge_frame.pack(fill='x')
        
        name_label = tk.Label(name_badge_frame, text=username,
                            font=('Helvetica', 12, 'bold'),
                            fg=self.colors['text'], bg=contact_frame['bg'])
        name_label.pack(side='left')
        
        # Badge de messages non lus
        unread_count = self.unread_counts.get(username, 0)
        if unread_count > 0:
            badge_frame = tk.Frame(name_badge_frame, 
                                 bg=self.colors['unread_badge'],
                                 padx=6, pady=2)
            badge_frame.pack(side='right', padx=(5, 0))
            
            badge_label = tk.Label(badge_frame, 
                                 text=str(unread_count),
                                 font=('Helvetica', 10, 'bold'),
                                 fg=self.colors['unread_text'],
                                 bg=self.colors['unread_badge'])
            badge_label.pack()
        
        # Dernier message
        last_msg = self.get_last_message(username)
        if last_msg:
            last_msg_label = tk.Label(info_frame, 
                                    text=last_msg[:30] + ('...' if len(last_msg) > 30 else ''),
                                    font=('Helvetica', 10),
                                    fg=self.colors['text_secondary'],
                                    bg=contact_frame['bg'])
            last_msg_label.pack(anchor='w', pady=(2, 0))
        
        # Status
        status_label = tk.Label(info_frame, text="● En ligne",
                              font=('Helvetica', 10),
                              fg='#4CAF50', bg=contact_frame['bg'])
        status_label.pack(anchor='w', pady=(2, 0))
        
        # Rendre cliquable
        def select_chat(e):
            self.select_chat(username)
            # Réinitialiser la couleur de fond
            for widget in self.contacts_frame.winfo_children():
                widget.config(bg=self.colors['sidebar'])
            contact_frame.config(bg=self.colors['selected'])
        
        for widget in [contact_frame, avatar_frame, avatar_label, 
                      info_frame, name_label, status_label]:
            widget.bind('<Button-1>', select_chat)
            widget.bind('<Enter>', lambda e, f=contact_frame: f.config(bg=self.colors['hover']) 
                       if username != self.current_chat else None)
            widget.bind('<Leave>', lambda e, f=contact_frame: f.config(bg=self.colors['selected']) 
                       if username == self.current_chat else f.config(bg=self.colors['sidebar']))
    
    def get_last_message(self, chat_id):
        """Récupère le dernier message d'une conversation"""
        if chat_id in self.messages and self.messages[chat_id]:
            last_msg = self.messages[chat_id][-1]
            content = last_msg.get('content', '')
            sender = last_msg.get('from', '')
            if sender:
                return f"{sender}: {content}"
            return content
        return None
    
    def select_group_chat(self):
        """Sélectionne le chat de groupe"""
        print("Chat de groupe sélectionné")
        self.current_chat = "groupe"
        
        # Réinitialiser le compteur de messages non lus
        if 'groupe' in self.unread_counts:
            self.unread_counts['groupe'] = 0
        
        # Mettre à jour l'en-tête
        self.chat_with_label.config(text="Chat général")
        self.chat_status_label.config(text=f"👥 {len(self.users)} participants")
        self.chat_avatar_label.config(text="👥")
        self.chat_avatar_frame.config(bg=self.colors['group_color'])
        
        # Activer la zone de message
        self.message_entry.config(state='normal')
        self.send_btn.config(state='normal')
        self.message_entry.focus()
        
        # Afficher l'historique
        self.display_chat_history()
        
        # Rafraîchir la liste des contacts pour enlever le badge
        self.filter_contacts()
    
    def select_chat(self, username):
        """Sélectionne un chat et réinitialise le compteur de non lus"""
        print(f"Chat sélectionné: {username}")
        self.current_chat = username
        
        # Réinitialiser le compteur de messages non lus
        if username in self.unread_counts:
            self.unread_counts[username] = 0
        
        # Mettre à jour l'en-tête
        self.chat_with_label.config(text=username)
        self.chat_status_label.config(text="● En ligne")
        self.chat_avatar_label.config(text=username[0].upper())
        self.chat_avatar_frame.config(bg='#2b5278')
        
        # Activer la zone de message
        self.message_entry.config(state='normal')
        self.send_btn.config(state='normal')
        self.message_entry.focus()
        
        # Afficher l'historique
        self.display_chat_history()
        
        # Rafraîchir la liste des contacts pour enlever le badge
        self.filter_contacts()
    
    def display_chat_history(self):
        """Affiche l'historique des messages"""
        # Effacer les messages actuels
        for widget in self.messages_container.winfo_children():
            widget.destroy()
        
        if self.current_chat and self.current_chat in self.messages:
            for msg in self.messages[self.current_chat]:
                self.display_message_widget(msg)
        
        # Scroll en bas
        self.root.after(100, self.scroll_to_bottom)
    
    def scroll_to_bottom(self):
        """Scroll vers le bas de la conversation"""
        self.messages_container.update_idletasks()
        self.messages_canvas.yview_moveto(1.0)
    
    def display_message_widget(self, message):
        """Affiche un message avec style Telegram"""
        msg_frame = tk.Frame(self.messages_container, bg=self.colors['chat_bg'])
        msg_frame.pack(fill='x', pady=(0, 15))
        
        # Déterminer si c'est un message envoyé par moi
        is_me = message.get('type') == 'private_sent' or message.get('from') == self.username
        is_group = message.get('type') == 'group'
        
        if is_me and not is_group:
            # Message privé envoyé (aligné à droite)
            bubble_frame = tk.Frame(msg_frame, bg=self.colors['chat_bg'])
            bubble_frame.pack(side='right')
            
            bubble = tk.Frame(bubble_frame, bg=self.colors['message_bg_me'],
                            padx=15, pady=10)
            bubble.pack()
            
            content = message.get('content', '')
            content_label = tk.Label(bubble, text=content,
                                   font=('Helvetica', 11),
                                   fg=self.colors['text'], 
                                   bg=bubble['bg'],
                                   wraplength=400, justify='left')
            content_label.pack()
            
            # Timestamp
            timestamp = message.get('timestamp', datetime.now().strftime('%H:%M'))
            time_label = tk.Label(bubble, text=timestamp,
                                font=('Helvetica', 8),
                                fg=self.colors['text_secondary'],
                                bg=bubble['bg'])
            time_label.pack(anchor='e')
            
        elif is_group:
            # Message de groupe
            # Avatar de l'expéditeur
            avatar_frame = tk.Frame(msg_frame, width=40, height=40,
                                  bg='#2b5278')
            avatar_frame.pack(side='left', padx=(0, 10))
            avatar_frame.pack_propagate(False)
            
            sender_initial = message.get('from', '?')[0].upper()
            avatar_label = tk.Label(avatar_frame, text=sender_initial,
                                  font=('Helvetica', 16, 'bold'),
                                  fg='white', bg='#2b5278')
            avatar_label.pack(expand=True)
            
            # Contenu du message
            content_frame = tk.Frame(msg_frame, bg=self.colors['chat_bg'])
            content_frame.pack(side='left', fill='both', expand=True)
            
            sender_label = tk.Label(content_frame, 
                                  text=message.get('from', ''),
                                  font=('Helvetica', 11, 'bold'),
                                  fg='#4CAF50', bg=self.colors['chat_bg'])
            sender_label.pack(anchor='w')
            
            bubble = tk.Frame(content_frame, bg=self.colors['message_bg_group'],
                            padx=15, pady=10)
            bubble.pack(anchor='w')
            
            content_label = tk.Label(bubble, text=message.get('content', ''),
                                   font=('Helvetica', 11),
                                   fg=self.colors['text'], 
                                   bg=bubble['bg'],
                                   wraplength=400, justify='left')
            content_label.pack()
            
            timestamp = message.get('timestamp', datetime.now().strftime('%H:%M'))
            time_label = tk.Label(bubble, text=timestamp,
                                font=('Helvetica', 8),
                                fg=self.colors['text_secondary'],
                                bg=bubble['bg'])
            time_label.pack(anchor='e')
            
        else:
            # Message privé reçu (aligné à gauche)
            avatar_frame = tk.Frame(msg_frame, width=40, height=40,
                                  bg='#2b5278')
            avatar_frame.pack(side='left', padx=(0, 10))
            avatar_frame.pack_propagate(False)
            
            sender_initial = message.get('from', '?')[0].upper()
            avatar_label = tk.Label(avatar_frame, text=sender_initial,
                                  font=('Helvetica', 16, 'bold'),
                                  fg='white', bg='#2b5278')
            avatar_label.pack(expand=True)
            
            content_frame = tk.Frame(msg_frame, bg=self.colors['chat_bg'])
            content_frame.pack(side='left', fill='both', expand=True)
            
            sender_label = tk.Label(content_frame, 
                                  text=message.get('from', ''),
                                  font=('Helvetica', 11, 'bold'),
                                  fg='#4CAF50', bg=self.colors['chat_bg'])
            sender_label.pack(anchor='w')
            
            bubble = tk.Frame(content_frame, bg=self.colors['message_bg_other'],
                            padx=15, pady=10)
            bubble.pack(anchor='w')
            
            content_label = tk.Label(bubble, text=message.get('content', ''),
                                   font=('Helvetica', 11),
                                   fg=self.colors['text'], 
                                   bg=bubble['bg'],
                                   wraplength=400, justify='left')
            content_label.pack()
            
            timestamp = message.get('timestamp', datetime.now().strftime('%H:%M'))
            time_label = tk.Label(bubble, text=timestamp,
                                font=('Helvetica', 8),
                                fg=self.colors['text_secondary'],
                                bg=bubble['bg'])
            time_label.pack(anchor='e')
    
    def connect_to_server(self):
        """Connexion au serveur avec configuration IP/Port"""
        username = self.username_entry.get().strip()
        server_ip = self.server_ip.get().strip()
        server_port = self.server_port.get().strip()
        
        if not username:
            self.status_label.config(text="Veuillez entrer un nom d'utilisateur")
            return
        
        if not server_ip:
            server_ip = '127.0.0.1'
        
        if not server_port:
            server_port = '5555'
        
        try:
            port = int(server_port)
        except ValueError:
            self.status_label.config(text="Port invalide")
            return
        
        try:
            self.socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            self.socket.connect((server_ip, port))
            
            # Envoyer le nom d'utilisateur
            self.socket.send(json.dumps({'username': username}).encode('utf-8'))
            
            # Attendre la réponse du serveur
            response = self.socket.recv(4096).decode('utf-8')
            data = json.loads(response)
            
            if data.get('type') == 'error':
                self.status_label.config(text=data['message'])
                self.socket.close()
                return
            
            if data.get('type') == 'welcome':
                self.username = username
                self.connected = True
                
                print(f"Connecté au serveur {server_ip}:{port} avec le nom: {self.username}")
                
                # Mettre à jour l'avatar
                self.avatar_label.config(text=self.username[0].upper())
                
                # Initialiser les messages du groupe
                self.messages['groupe'] = []
                
                # Passer à l'interface de chat
                self.login_frame.place_forget()
                self.main_frame.pack(fill='both', expand=True)
                
                self.username_label.config(text=self.username)
                
                # Démarrer le thread de réception
                receive_thread = threading.Thread(target=self.receive_messages, daemon=True)
                receive_thread.start()
                
        except ConnectionRefusedError:
            self.status_label.config(text=f"Connexion refusée: {server_ip}:{port}")
        except socket.gaierror:
            self.status_label.config(text=f"Adresse IP invalide: {server_ip}")
        except Exception as e:
            self.status_label.config(text=f"Erreur de connexion: {str(e)}")
            print(f"Erreur de connexion: {e}")
    
    def receive_messages(self):
        """Réception des messages du serveur"""
        while self.connected:
            try:
                data = self.socket.recv(4096).decode('utf-8')
                if not data:
                    print("Connexion fermée par le serveur")
                    break
                
                message = json.loads(data)
                print(f"Message reçu: {message.get('type')}")
                self.process_message(message)
                
            except json.JSONDecodeError as e:
                print(f"Erreur JSON: {e}")
                continue
            except Exception as e:
                print(f"Erreur réception: {e}")
                break
        
        self.connected = False
        
    def process_message(self, message):
        """Traite les messages reçus"""
        msg_type = message.get('type')
        
        if msg_type == 'user_list':
            self.update_user_list(message['users'])
        
        elif msg_type == 'private':
            print(f"Message privé reçu de {message.get('from')}: {message.get('content')}")
            self.display_private_message(message)
        
        elif msg_type == 'group':
            print(f"Message de groupe reçu de {message.get('from')}: {message.get('content')}")
            self.display_group_message(message)
        
        elif msg_type == 'private_sent':
            print(f"Confirmation d'envoi à {message.get('to')}")
            self.display_private_message(message, sent=True)
        
        elif msg_type == 'group_sent':
            print(f"Confirmation d'envoi au groupe")
            self.display_group_message(message, sent=True)
        
        elif msg_type == 'error':
            self.root.after(0, lambda: messagebox.showerror("Erreur", message['message']))
    
    def update_user_list(self, users):
        """Met à jour la liste des utilisateurs"""
        print(f"Mise à jour liste utilisateurs: {users}")
        old_users = self.users.copy()
        self.users = users
        
        # Mettre à jour l'affichage des contacts
        self.root.after(0, self.filter_contacts)
        
        # Mettre à jour le statut du chat de groupe
        if self.current_chat == "groupe":
            self.root.after(0, lambda: self.chat_status_label.config(
                text=f"👥 {len(self.users)} participants"))
        
        # Initialiser les compteurs pour les nouveaux utilisateurs
        for user in users:
            if user != self.username and user not in self.messages:
                self.messages[user] = []
                self.unread_counts[user] = 0
        
        # Nettoyer les utilisateurs déconnectés
        for user in old_users:
            if user not in users and user != self.username:
                if user in self.unread_counts:
                    del self.unread_counts[user]
                if user in self.messages:
                    del self.messages[user]
        
        # Si le contact actuel s'est déconnecté
        if self.current_chat and self.current_chat not in users and self.current_chat != "groupe":
            self.root.after(0, self.reset_current_chat)
    
    def reset_current_chat(self):
        """Réinitialise le chat courant"""
        self.current_chat = None
        self.chat_with_label.config(text="Sélectionnez un contact")
        self.chat_status_label.config(text="")
        self.chat_avatar_label.config(text="")
        self.chat_avatar_frame.config(bg='#2b5278')
        self.message_entry.config(state='disabled')
        self.send_btn.config(state='disabled')
        
        # Effacer les messages
        for widget in self.messages_container.winfo_children():
            widget.destroy()
    
    def display_private_message(self, message, sent=False):
        """Affiche un message privé avec gestion des non lus"""
        if sent:
            # Message envoyé par moi
            recipient = message.get('to')
            if recipient in self.messages:
                self.messages[recipient].append(message)
            
            # Afficher immédiatement si c'est le chat actuel
            if recipient == self.current_chat:
                self.root.after(0, lambda: self.display_message_widget(message))
                self.root.after(0, self.scroll_to_bottom)
        else:
            # Message reçu
            sender = message.get('from')
            
            if sender not in self.messages:
                self.messages[sender] = []
            
            self.messages[sender].append(message)
            
            # Incrémenter le compteur de non lus si ce n'est pas le chat actuel
            if sender != self.current_chat:
                self.unread_counts[sender] = self.unread_counts.get(sender, 0) + 1
                
                # Notification visuelle
                self.root.after(0, lambda: self.root.title(f"📩 {self.unread_counts[sender]} nouveau message"))
                self.root.after(3000, lambda: self.root.title("Chat Application"))
                
                # Mettre à jour le badge
                self.root.after(0, self.filter_contacts)
            else:
                # Afficher immédiatement si c'est le chat actuel
                self.root.after(0, lambda: self.display_message_widget(message))
                self.root.after(0, self.scroll_to_bottom)
    
    def display_group_message(self, message, sent=False):
        """Affiche un message de groupe avec gestion des non lus"""
        # Ajouter le message à l'historique du groupe
        if 'groupe' not in self.messages:
            self.messages['groupe'] = []
        
        self.messages['groupe'].append(message)
        
        if not sent:
            # Incrémenter le compteur de non lus si ce n'est pas le chat du groupe actuel
            if self.current_chat != "groupe":
                self.unread_counts['groupe'] = self.unread_counts.get('groupe', 0) + 1
                
                # Notification visuelle
                total_unread = sum(self.unread_counts.values())
                self.root.after(0, lambda: self.root.title(f"📩 {total_unread} nouveaux messages"))
                self.root.after(3000, lambda: self.root.title("Chat Application"))
                
                # Mettre à jour le badge
                self.root.after(0, self.filter_contacts)
            else:
                # Afficher immédiatement si c'est le chat du groupe actuel
                self.root.after(0, lambda: self.display_message_widget(message))
                self.root.after(0, self.scroll_to_bottom)
        else:
            # Message de groupe envoyé par moi
            if self.current_chat == "groupe":
                self.root.after(0, lambda: self.display_message_widget(message))
                self.root.after(0, self.scroll_to_bottom)
    
    def send_message(self):
        """Envoie un message (privé ou de groupe)"""
        if not self.current_chat:
            messagebox.showinfo("Information", "Sélectionnez d'abord un contact ou le chat de groupe")
            return
        
        content = self.message_entry.get('1.0', 'end-1c').strip()
        if not content:
            return
        
        if self.current_chat == "groupe":
            # Message de groupe
            print(f"Envoi du message de groupe: {content}")
            message = {
                'type': 'group',
                'content': content
            }
        else:
            # Message privé
            print(f"Envoi du message privé à {self.current_chat}: {content}")
            message = {
                'type': 'private',
                'recipient': self.current_chat,
                'content': content
            }
        
        try:
            self.socket.send(json.dumps(message).encode('utf-8'))
            self.message_entry.delete('1.0', 'end')
        except Exception as e:
            print(f"Erreur envoi: {e}")
            messagebox.showerror("Erreur", "Impossible d'envoyer le message")
    
    def send_message_event(self, event):
        """Gère l'envoi avec Entrée"""
        if not event.state & 0x1:  # Shift non pressé
            self.send_message()
            return 'break'
    
    def logout(self):
        """Déconnexion propre"""
        print(f"Déconnexion de {self.username}")
        
        # Marquer comme déconnecté
        self.connected = False
        
        # Fermer la socket
        if self.socket:
            try:
                self.socket.close()
            except:
                pass
        
        # Réinitialiser l'état
        self.current_chat = None
        self.users = []
        self.messages = {}
        self.unread_counts = {}
        
        # Revenir à l'écran de connexion
        self.main_frame.pack_forget()
        self.login_frame.place(relx=0.5, rely=0.5, anchor='center')
        self.username_entry.delete(0, 'end')
        self.username_entry.focus()
        self.status_label.config(text="")
        
        print("Déconnexion réussie")
    
    def on_closing(self):
        """Gestion de la fermeture de la fenêtre"""
        if self.connected:
            if messagebox.askokcancel("Quitter", "Voulez-vous vraiment quitter?"):
                self.logout()
                self.root.destroy()
        else:
            self.root.destroy()
    
    def run(self):
        self.root.mainloop()

if __name__ == "__main__":
    client = ChatClient()
    client.run()