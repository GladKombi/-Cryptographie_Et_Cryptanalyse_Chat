<?php
require_once 'config.php';

// Vérifier si un mode sombre est activé
$darkMode = isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'true';
?>
<!DOCTYPE html>
<html lang="fr" class="<?php echo $darkMode ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CryptoApp - Chiffrement/Déchiffrement AES & DES</title>
    <!-- Tailwind CSS via CDN avec support dark mode -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            100: '#1e293b',
                            200: '#0f172a',
                            300: '#020617',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Police Google -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        .crypto-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .crypto-bg-dark {
            background: linear-gradient(135deg, #4c51bf 0%, #7c3aed 100%);
        }
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        .dark .card-hover:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        .algorithm-active {
            border-color: #667eea;
            background-color: rgba(102, 126, 234, 0.05);
        }
        .dark .algorithm-active {
            border-color: #818cf8;
            background-color: rgba(129, 140, 248, 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.2);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(245, 87, 108, 0.2);
        }
        .key-generator-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            transition: all 0.3s ease;
        }
        .key-generator-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
        }
        /* Animation pour la génération de clé */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .generating {
            animation: pulse 0.5s ease-in-out;
        }
        /* Transition pour le mode sombre */
        * {
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- En-tête -->
    <header class="crypto-bg dark:crypto-bg-dark text-white shadow-lg">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-6 md:mb-0">
                    <h1 class="text-3xl md:text-4xl font-bold mb-2"><i class="fas fa-lock mr-3"></i>CryptoApp</h1>
                    <p class="text-lg opacity-90">Chiffrement et déchiffrement sécurisé avec AES & DES</p>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Bouton Mode Sombre/Clair -->
                    <button id="darkModeToggle" class="bg-white bg-opacity-20 p-3 rounded-full hover:bg-opacity-30 transition">
                        <i class="fas <?php echo $darkMode ? 'fa-sun' : 'fa-moon'; ?>"></i>
                    </button>
                    <div class="bg-white bg-opacity-10 p-4 rounded-lg">
                        <h2 class="text-xl font-semibold mb-2"><i class="fas fa-shield-alt mr-2"></i>Sécurité maximale</h2>
                        <p class="text-sm">Algorithmes cryptographiques robustes</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Contenu principal -->
    <main class="container mx-auto px-4 py-8">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="mb-8 p-4 rounded-lg <?php echo $_SESSION['message_type'] == 'success' ? 'bg-green-100 dark:bg-green-900 dark:text-green-100 text-green-800 border border-green-200 dark:border-green-700' : 'bg-red-100 dark:bg-red-900 dark:text-red-100 text-red-800 border border-red-200 dark:border-red-700'; ?>">
                <div class="flex items-center">
                    <i class="fas <?php echo $_SESSION['message_type'] == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-3 text-xl"></i>
                    <p><?php echo $_SESSION['message']; ?></p>
                </div>
                <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
            </div>
        <?php endif; ?>

        <!-- Afficher les résultats si disponibles -->
        <?php if (isset($_SESSION['encryption_result'])): ?>
            <div class="mb-8 bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 result-display">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white flex items-center">
                        <i class="fas fa-lock text-green-500 mr-3"></i> Message chiffré avec <?php echo strtoupper($_SESSION['encryption_algorithm']); ?>
                    </h3>
                    <button onclick="copyToClipboard('encryptionResult')" class="text-sm bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100 px-3 py-1 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-800">
                        <i class="fas fa-copy mr-1"></i> Copier
                    </button>
                </div>
                <div id="encryptionResult" class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg overflow-x-auto">
                    <code class="text-sm text-gray-800 dark:text-gray-200"><?php echo htmlspecialchars($_SESSION['encryption_result']); ?></code>
                </div>
                <?php unset($_SESSION['encryption_result']); unset($_SESSION['encryption_algorithm']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['decryption_result'])): ?>
            <div class="mb-8 bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 result-display">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white flex items-center">
                        <i class="fas fa-unlock text-green-500 mr-3"></i> Message déchiffré avec <?php echo strtoupper($_SESSION['decryption_algorithm']); ?>
                    </h3>
                    <button onclick="copyToClipboard('decryptionResult')" class="text-sm bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100 px-3 py-1 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-800">
                        <i class="fas fa-copy mr-1"></i> Copier
                    </button>
                </div>
                <div id="decryptionResult" class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <p class="text-gray-800 dark:text-gray-200"><?php echo htmlspecialchars($_SESSION['decryption_result']); ?></p>
                </div>
                <?php unset($_SESSION['decryption_result']); unset($_SESSION['decryption_algorithm']); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <!-- Section de chiffrement -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden card-hover">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-6">
                    <h2 class="text-2xl font-bold text-white"><i class="fas fa-lock mr-3"></i>Chiffrer un message</h2>
                </div>
                <form action="encrypt.php" method="POST" class="p-6">
                    <!-- Sélection de l'algorithme -->
                    <div class="mb-6">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-3">Choisissez un algorithme :</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="algorithm-selector">
                                <input type="radio" id="aes-encrypt" name="algorithm" value="aes" class="hidden" checked>
                                <label for="aes-encrypt" class="flex items-center justify-center p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer algorithm-card dark:text-gray-300">
                                    <div class="text-center">
                                        <i class="fas fa-key text-2xl text-indigo-600 dark:text-indigo-400 mb-2"></i>
                                        <h3 class="font-bold">AES-256</h3>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Standard avancé</p>
                                    </div>
                                </label>
                            </div>
                            <div class="algorithm-selector">
                                <input type="radio" id="des-encrypt" name="algorithm" value="des" class="hidden">
                                <label for="des-encrypt" class="flex items-center justify-center p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer algorithm-card dark:text-gray-300">
                                    <div class="text-center">
                                        <i class="fas fa-unlock-alt text-2xl text-purple-600 dark:text-purple-400 mb-2"></i>
                                        <h3 class="font-bold">Triple DES</h3>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">3 passes de chiffrement</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Clé de chiffrement -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <label for="key" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold">
                                <i class="fas fa-key mr-1"></i>Clé de chiffrement :
                            </label>
                            <button type="button" id="generateKeyBtn" class="key-generator-btn text-white text-xs px-3 py-1 rounded-lg flex items-center">
                                <i class="fas fa-dice mr-1"></i> Générer une clé
                            </button>
                        </div>
                        <div class="relative">
                            <input type="password" id="key" name="key" 
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent"
                                   placeholder="Entrez votre clé secrète (laisser vide pour utiliser la clé par défaut)">
                            <div class="absolute right-12 top-3 flex items-center">
                                <span id="keyLength" class="text-xs text-gray-500 dark:text-gray-400 mr-2">0/32</span>
                                <button type="button" id="toggleKey" class="text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="flex justify-between mt-2">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Pour AES : 16, 24 ou 32 caractères. Pour DES : 8 caractères.</p>
                            <button type="button" id="suggestKeyBtn" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                <i class="fas fa-lightbulb mr-1"></i> Suggérer une longueur
                            </button>
                        </div>
                    </div>

                    <!-- Message à chiffrer -->
                    <div class="mb-6">
                        <label for="plaintext" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">
                            <i class="fas fa-edit mr-1"></i>Message à chiffrer :
                        </label>
                        <textarea id="plaintext" name="plaintext" rows="5" 
                                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent"
                                  placeholder="Entrez le texte que vous souhaitez chiffrer..." required></textarea>
                    </div>

                    <button type="submit" class="w-full py-3 px-6 btn-primary text-white font-semibold rounded-lg shadow-md flex items-center justify-center">
                        <i class="fas fa-lock mr-3"></i> Chiffrer le message
                    </button>
                </form>
            </div>

            <!-- Section de déchiffrement -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden card-hover">
                <div class="bg-gradient-to-r from-purple-500 to-pink-600 p-6">
                    <h2 class="text-2xl font-bold text-white"><i class="fas fa-unlock mr-3"></i>Déchiffrer un message</h2>
                </div>
                <form action="decrypt.php" method="POST" class="p-6">
                    <!-- Sélection de l'algorithme -->
                    <div class="mb-6">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-3">Choisissez un algorithme :</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="algorithm-selector">
                                <input type="radio" id="aes-decrypt" name="algorithm" value="aes" class="hidden" checked>
                                <label for="aes-decrypt" class="flex items-center justify-center p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer algorithm-card dark:text-gray-300">
                                    <div class="text-center">
                                        <i class="fas fa-key text-2xl text-indigo-600 dark:text-indigo-400 mb-2"></i>
                                        <h3 class="font-bold">AES-256</h3>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Standard avancé</p>
                                    </div>
                                </label>
                            </div>
                            <div class="algorithm-selector">
                                <input type="radio" id="des-decrypt" name="algorithm" value="des" class="hidden">
                                <label for="des-decrypt" class="flex items-center justify-center p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer algorithm-card dark:text-gray-300">
                                    <div class="text-center">
                                        <i class="fas fa-unlock-alt text-2xl text-purple-600 dark:text-purple-400 mb-2"></i>
                                        <h3 class="font-bold">Triple DES</h3>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">3 passes de chiffrement</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Clé de déchiffrement -->
                    <div class="mb-6">
                        <label for="decrypt_key" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">
                            <i class="fas fa-key mr-1"></i>Clé de déchiffrement :
                        </label>
                        <div class="relative">
                            <input type="password" id="decrypt_key" name="key" 
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent"
                                   placeholder="Entrez la clé utilisée pour le chiffrement">
                            <div class="absolute right-3 top-3 flex items-center">
                                <button type="button" id="toggleDecryptKey" class="text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Doit correspondre à la clé utilisée pour le chiffrement.</p>
                    </div>

                    <!-- Message à déchiffrer -->
                    <div class="mb-6">
                        <label for="ciphertext" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">
                            <i class="fas fa-lock mr-1"></i>Message à déchiffrer :
                        </label>
                        <textarea id="ciphertext" name="ciphertext" rows="5" 
                                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent"
                                  placeholder="Entrez le texte chiffré (format base64)..." required></textarea>
                    </div>

                    <button type="submit" class="w-full py-3 px-6 btn-secondary text-white font-semibold rounded-lg shadow-md flex items-center justify-center">
                        <i class="fas fa-unlock mr-3"></i> Déchiffrer le message
                    </button>
                </form>
            </div>
        </div>

        <!-- Section d'information -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6 flex items-center">
                <i class="fas fa-info-circle mr-3 text-indigo-600 dark:text-indigo-400"></i> Informations sur les algorithmes
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="p-5 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-900/30 dark:to-blue-900/30 rounded-lg border border-indigo-100 dark:border-indigo-800">
                    <h3 class="text-xl font-bold text-indigo-800 dark:text-indigo-300 mb-3">AES (Advanced Encryption Standard)</h3>
                    <ul class="space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                            <span class="text-gray-700 dark:text-gray-300">Chiffrement par blocs de 128 bits</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                            <span class="text-gray-700 dark:text-gray-300">Clés de 128, 192 ou 256 bits</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                            <span class="text-gray-700 dark:text-gray-300">Standard recommandé par le NIST</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                            <span class="text-gray-700 dark:text-gray-300">Utilisé pour les données classifiées</span>
                        </li>
                    </ul>
                </div>
                <div class="p-5 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/30 dark:to-pink-900/30 rounded-lg border border-purple-100 dark:border-purple-800">
                    <h3 class="text-xl font-bold text-purple-800 dark:text-purple-300 mb-3">DES (Data Encryption Standard)</h3>
                    <ul class="space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                            <span class="text-gray-700 dark:text-gray-300">Chiffrement par blocs de 64 bits</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                            <span class="text-gray-700 dark:text-gray-300">Clé de 56 bits (Triple DES : 112 ou 168 bits)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                            <span class="text-gray-700 dark:text-gray-300">Ancien standard, encore utilisé dans certains systèmes</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-2 mt-1"></i>
                            <span class="text-gray-700 dark:text-gray-300">Considéré comme moins sécurisé qu'AES</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Section historique (si disponible) -->
        <?php if (isset($_SESSION['history']) && !empty($_SESSION['history'])): ?>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6 flex items-center">
                    <i class="fas fa-history mr-3 text-indigo-600 dark:text-indigo-400"></i> Historique récent
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th class="py-3 px-4 text-left text-gray-800 dark:text-gray-200">Date</th>
                                <th class="py-3 px-4 text-left text-gray-800 dark:text-gray-200">Opération</th>
                                <th class="py-3 px-4 text-left text-gray-800 dark:text-gray-200">Algorithme</th>
                                <th class="py-3 px-4 text-left text-gray-800 dark:text-gray-200">Résultat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $history = array_slice($_SESSION['history'], -5); // 5 dernières entrées
                            foreach ($history as $entry): 
                            ?>
                                <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="py-3 px-4 text-gray-700 dark:text-gray-300"><?php echo $entry['date']; ?></td>
                                    <td class="py-3 px-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $entry['operation'] == 'chiffrement' ? 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100' : 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-100'; ?>">
                                            <?php echo $entry['operation']; ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-700 dark:text-gray-300"><?php echo strtoupper($entry['algorithm']); ?></td>
                                    <td class="py-3 px-4">
                                        <div class="truncate max-w-xs text-gray-700 dark:text-gray-300" title="<?php echo htmlspecialchars($entry['result']); ?>">
                                            <?php echo substr($entry['result'], 0, 50); ?>...
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-right">
                    <button id="clearHistory" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                        <i class="fas fa-trash-alt mr-1"></i> Effacer l'historique
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Pied de page -->
    <footer class="crypto-bg dark:crypto-bg-dark text-white py-8 mt-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-6 md:mb-0">
                    <h3 class="text-xl font-bold mb-2"><i class="fas fa-lock mr-2"></i>CryptoApp</h3>
                    <p class="opacity-90">Application de chiffrement sécurisée</p>
                </div>
                <div class="text-center">
                    <p class="mb-2">Développé avec PHP, AES, DES et Tailwind CSS</p>
                    <p class="text-sm opacity-80">© 2023 - Application à des fins éducatives</p>
                </div>
                <div class="mt-6 md:mt-0">
                    <div class="flex space-x-4">
                        <a href="#" class="bg-white bg-opacity-20 p-3 rounded-full hover:bg-opacity-30 transition">
                            <i class="fab fa-github"></i>
                        </a>
                        <a href="#" class="bg-white bg-opacity-20 p-3 rounded-full hover:bg-opacity-30 transition">
                            <i class="fas fa-code"></i>
                        </a>
                        <a href="#" class="bg-white bg-opacity-20 p-3 rounded-full hover:bg-opacity-30 transition">
                            <i class="fas fa-shield-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript pour les interactions -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ========== GESTION DU MODE SOMBRE ==========
            const darkModeToggle = document.getElementById('darkModeToggle');
            const darkModeIcon = darkModeToggle.querySelector('i');
            
            function setDarkMode(isDark) {
                if (isDark) {
                    document.documentElement.classList.add('dark');
                    darkModeIcon.classList.remove('fa-moon');
                    darkModeIcon.classList.add('fa-sun');
                } else {
                    document.documentElement.classList.remove('dark');
                    darkModeIcon.classList.remove('fa-sun');
                    darkModeIcon.classList.add('fa-moon');
                }
                
                // Sauvegarder la préférence dans un cookie (30 jours)
                const expires = new Date();
                expires.setTime(expires.getTime() + (30 * 24 * 60 * 60 * 1000));
                document.cookie = `darkMode=${isDark}; expires=${expires.toUTCString()}; path=/`;
            }
            
            darkModeToggle.addEventListener('click', function() {
                const isDark = document.documentElement.classList.contains('dark');
                setDarkMode(!isDark);
            });
            
            // ========== GÉNÉRATION DE CLÉS ALÉATOIRES ==========
            const generateKeyBtn = document.getElementById('generateKeyBtn');
            const keyInput = document.getElementById('key');
            const algorithmRadios = document.querySelectorAll('input[name="algorithm"]');
            const keyLengthSpan = document.getElementById('keyLength');
            const suggestKeyBtn = document.getElementById('suggestKeyBtn');
            
            // Fonction pour générer une clé aléatoire
            function generateRandomKey(length) {
                const charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
                let key = '';
                for (let i = 0; i < length; i++) {
                    const randomIndex = Math.floor(Math.random() * charset.length);
                    key += charset[randomIndex];
                }
                return key;
            }
            
            // Fonction pour obtenir l'algorithme sélectionné
            function getSelectedAlgorithm() {
                for (const radio of algorithmRadios) {
                    if (radio.checked) {
                        return radio.value;
                    }
                }
                return 'aes'; // Par défaut
            }
            
            // Fonction pour mettre à jour l'indicateur de longueur
            function updateKeyLengthIndicator() {
                const length = keyInput.value.length;
                const algorithm = getSelectedAlgorithm();
                let maxLength = 32; // AES par défaut
                
                if (algorithm === 'des') {
                    maxLength = 8;
                }
                
                keyLengthSpan.textContent = `${length}/${maxLength}`;
                
                // Changer la couleur selon la validité
                if ((algorithm === 'aes' && (length === 16 || length === 24 || length === 32)) ||
                    (algorithm === 'des' && length === 8)) {
                    keyLengthSpan.className = 'text-xs text-green-600 dark:text-green-400 mr-2';
                } else if (length === 0) {
                    keyLengthSpan.className = 'text-xs text-gray-500 dark:text-gray-400 mr-2';
                } else {
                    keyLengthSpan.className = 'text-xs text-red-600 dark:text-red-400 mr-2';
                }
            }
            
            // Générer une clé selon l'algorithme
            generateKeyBtn.addEventListener('click', function() {
                const algorithm = getSelectedAlgorithm();
                let keyLength;
                
                if (algorithm === 'aes') {
                    // Pour AES, proposer une longueur de 32 caractères (256 bits)
                    keyLength = 32;
                } else {
                    // Pour DES, 8 caractères
                    keyLength = 8;
                }
                
                // Animation de génération
                generateKeyBtn.classList.add('generating');
                setTimeout(() => {
                    const newKey = generateRandomKey(keyLength);
                    keyInput.value = newKey;
                    keyInput.type = 'text'; // Afficher la clé générée
                    
                    // Mettre à jour l'indicateur de longueur
                    updateKeyLengthIndicator();
                    
                    // Animation de fin
                    setTimeout(() => {
                        generateKeyBtn.classList.remove('generating');
                    }, 500);
                    
                    // Notification
                    showNotification(`Clé ${algorithm.toUpperCase()} de ${keyLength} caractères générée !`, 'success');
                }, 300);
            });
            
            // Suggérer une longueur appropriée
            suggestKeyBtn.addEventListener('click', function() {
                const algorithm = getSelectedAlgorithm();
                let suggestions;
                
                if (algorithm === 'aes') {
                    suggestions = '16, 24 ou 32 caractères';
                } else {
                    suggestions = '8 caractères';
                }
                
                showNotification(`Pour ${algorithm.toUpperCase()}, utilisez une clé de ${suggestions}`, 'info');
            });
            
            // Mettre à jour l'indicateur de longueur en temps réel
            keyInput.addEventListener('input', updateKeyLengthIndicator);
            
            // Mettre à jour lors du changement d'algorithme
            algorithmRadios.forEach(radio => {
                radio.addEventListener('change', updateKeyLengthIndicator);
            });
            
            // Initialiser l'indicateur
            updateKeyLengthIndicator();
            
            // ========== AFFICHAGE/MASQUAGE DES CLÉS ==========
            const toggleKeyBtn = document.getElementById('toggleKey');
            const toggleDecryptKeyBtn = document.getElementById('toggleDecryptKey');
            const decryptKeyInput = document.getElementById('decrypt_key');
            
            if (toggleKeyBtn) {
                toggleKeyBtn.addEventListener('click', function() {
                    const type = keyInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    keyInput.setAttribute('type', type);
                    this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
                });
            }
            
            if (toggleDecryptKeyBtn) {
                toggleDecryptKeyBtn.addEventListener('click', function() {
                    const type = decryptKeyInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    decryptKeyInput.setAttribute('type', type);
                    this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
                });
            }
            
            // ========== SÉLECTION VISUELLE DES ALGORITHMES ==========
            const algorithmCards = document.querySelectorAll('.algorithm-card');
            algorithmCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Retirer la classe active de toutes les cartes
                    algorithmCards.forEach(c => c.classList.remove('algorithm-active', 'border-indigo-500', 'dark:border-indigo-400'));
                    
                    // Ajouter la classe active à la carte cliquée
                    this.classList.add('algorithm-active', 'border-indigo-500', 'dark:border-indigo-400');
                    
                    // Cochez l'input radio correspondant
                    const inputId = this.getAttribute('for');
                    document.getElementById(inputId).checked = true;
                    
                    // Mettre à jour l'indicateur de longueur de clé
                    updateKeyLengthIndicator();
                });
            });
            
            // Initialiser la première carte comme active
            if (algorithmCards.length > 0) {
                algorithmCards[0].classList.add('algorithm-active', 'border-indigo-500', 'dark:border-indigo-400');
            }
            
            // ========== FONCTIONS UTILITAIRES ==========
            function showNotification(message, type = 'info') {
                // Créer une notification temporaire
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transform transition-transform duration-300 ${
                    type === 'success' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100' :
                    type === 'error' ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-100' :
                    'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100'
                }`;
                notification.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas ${
                            type === 'success' ? 'fa-check-circle' :
                            type === 'error' ? 'fa-exclamation-triangle' :
                            'fa-info-circle'
                        } mr-3"></i>
                        <span>${message}</span>
                    </div>
                `;
                
                document.body.appendChild(notification);
                
                // Supprimer après 3 secondes
                setTimeout(() => {
                    notification.style.transform = 'translateX(100%)';
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            }
            
            function copyToClipboard(elementId) {
                const element = document.getElementById(elementId);
                let textToCopy;
                
                if (element.tagName === 'TEXTAREA' || element.tagName === 'INPUT') {
                    textToCopy = element.value;
                } else {
                    textToCopy = element.textContent || element.innerText;
                }
                
                navigator.clipboard.writeText(textToCopy).then(() => {
                    showNotification('Copié dans le presse-papier !', 'success');
                }).catch(err => {
                    console.error('Erreur lors de la copie : ', err);
                    showNotification('Erreur lors de la copie', 'error');
                });
            }
            
            // Exposer la fonction pour les boutons de copie
            window.copyToClipboard = copyToClipboard;
            
            // ========== EFFACER L'HISTORIQUE ==========
            const clearHistoryBtn = document.getElementById('clearHistory');
            if (clearHistoryBtn) {
                clearHistoryBtn.addEventListener('click', function() {
                    if (confirm('Êtes-vous sûr de vouloir effacer l\'historique ?')) {
                        window.location.href = '?clear_history=1';
                    }
                });
            }
            
            // ========== VALIDATION DES FORMULAIRES ==========
            const encryptForm = document.querySelector('form[action="encrypt.php"]');
            const decryptForm = document.querySelector('form[action="decrypt.php"]');
            
            function validateKey(key, algorithm) {
                if (!key.trim()) return true; // Clé vide, utilisation de la clé par défaut
                
                if (algorithm === 'aes') {
                    return key.length === 16 || key.length === 24 || key.length === 32;
                } else if (algorithm === 'des') {
                    return key.length === 8;
                }
                return false;
            }
            
            if (encryptForm) {
                encryptForm.addEventListener('submit', function(e) {
                    const algorithm = getSelectedAlgorithm();
                    const key = keyInput.value;
                    
                    if (key && !validateKey(key, algorithm)) {
                        e.preventDefault();
                        showNotification(`Clé invalide pour ${algorithm.toUpperCase()} : ${algorithm === 'aes' ? '16, 24 ou 32 caractères requis' : '8 caractères requis'}`, 'error');
                    }
                });
            }
            
            if (decryptForm) {
                decryptForm.addEventListener('submit', function(e) {
                    const algorithm = document.querySelector('form[action="decrypt.php"] input[name="algorithm"]:checked').value;
                    const key = decryptKeyInput.value;
                    
                    if (key && !validateKey(key, algorithm)) {
                        e.preventDefault();
                        showNotification(`Clé invalide pour ${algorithm.toUpperCase()} : ${algorithm === 'aes' ? '16, 24 ou 32 caractères requis' : '8 caractères requis'}`, 'error');
                    }
                });
            }
        });
    </script>
</body>
</html>