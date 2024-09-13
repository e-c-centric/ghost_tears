<?php
$host = 'localhost';  // Database host
$db   = 'ghosttears'; // Database name
$user = 'root';       // database username
$pass = '';           // database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'new_user') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Check if username already exists
        $stmt = $pdo->prepare("SELECT user_id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            echo json_encode(['status' => 'error', 'message' => 'Username already exists']);
            exit;
        }

        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
        echo json_encode(['status' => 'success', 'message' => 'User created successfully']);
    } elseif ($action === 'login') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Check if username exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid username']);
            exit;
        }

        // Check if password is correct
        if (password_verify($password, $user['password'])) {
            echo json_encode(['status' => 'success', 'message' => 'Login successful', 'id' => $user['user_id']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
        }
    } elseif ($action === 'new_game') {
        $category = $_POST['category'];
        $mode = $_POST['mode'];
        $player = $_POST['creator'];

        //get category id
        $stmt = $pdo->prepare("SELECT category_id FROM categories WHERE category_name = ?");
        $stmt->execute([$category]);
        $category = $stmt->fetch();
        $category_id = $category['category_id'];

        $stmt = $pdo->prepare("INSERT INTO games (category_id,`status`,current_word) VALUES (?, ?, ?)");
        $stmt->execute([$category_id, 'ongoing', '']);
        $game_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO player_game (game_id, user_id, turn_order) VALUES (?, ?, ?)");
        $stmt->execute([$game_id, $player, 1]);

        echo json_encode(['status' => 'success', 'message' => 'Game created successfully', 'game_id' => $game_id]);
    } elseif ($action === 'join_game') {
        $game_id = $_POST['game_id'];
        $player = $_POST['player'];

        $stmt = $pdo->prepare("SELECT game_id, category_id, status, current_word FROM games WHERE game_id = ?");
        $stmt->execute([$game_id]);
        $game = $stmt->fetch();

        if (!$game) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid game id']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT user_id, turn_order FROM player_game WHERE game_id = ?");
        $stmt->execute([$game_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        $next_order = $order['turn_order'] + 1;

        $stmt = $pdo->prepare("INSERT INTO player_game (game_id, user_id, turn_order) VALUES (?, ?, ?)");
        $stmt->execute([$game_id, $player, $next_order]);

        echo json_encode(['status' => 'success', 'message' => 'Joined game successfully']);
    } elseif ($action === 'get_game') {
        $game_id = $_POST['game_id'];
        $stmt = $pdo->prepare("
            SELECT 
                g.*, 
                pg.user_id, pg.turn_order, 
                gw.word, gw.last_guesser 
            FROM games g
            LEFT JOIN player_game pg ON g.game_id = pg.game_id
            LEFT JOIN words_used gw ON g.game_id = gw.game_id
            WHERE g.game_id = ?
        ");
        $stmt->execute([$game_id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$result) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid game id']);
            exit;
        }

        $game = [];
        $players = [];
        $words = [];

        foreach ($result as $row) {
            if (empty($game)) {
                $game = [
                    'game_id' => $row['game_id'],
                    'category_id' => $row['category_id'],
                    'status' => $row['status'],
                    'current_word' => $row['current_word']
                ];
            }
            if (!in_array(['user_id' => $row['user_id'], 'turn_order' => $row['turn_order']], $players)) {
                $players[] = [
                    'user_id' => $row['user_id'],
                    'turn_order' => $row['turn_order']
                ];
            }
            if (!in_array(['word' => $row['word'], 'last_guesser' => $row['last_guesser']], $words)) {
                $words[] = [
                    'word' => $row['word'],
                    'last_guesser' => $row['last_guesser']
                ];
            }
        }
        echo json_encode(['status' => 'success', 'game' => $game, 'players' => $players, 'words' => $words]);
    } elseif ($action === 'show_games') {
        $stmt = $pdo->prepare("
            SELECT 
                g.game_id, g.category_id, g.status, g.current_word, 
                pg.user_id, pg.turn_order 
            FROM games g
            LEFT JOIN player_game pg ON g.game_id = pg.game_id
        ");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $games = [];
        foreach ($results as $row) {
            $game_id = $row['game_id'];
            if (!isset($games[$game_id])) {
                $games[$game_id] = [
                    'game_id' => $row['game_id'],
                    'category_id' => $row['category_id'],
                    'status' => $row['status'],
                    'current_word' => $row['current_word'],
                    'players' => []
                ];
            }
            $games[$game_id]['players'][] = [
                'user_id' => $row['user_id'],
                'turn_order' => $row['turn_order']
            ];
        }

        echo json_encode(['status' => 'success', 'games' => array_values($games)]);
    } elseif ($action === 'get_categories') {

        $stmt = $pdo->prepare("SELECT category_id, category_name FROM categories");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'categories' => $categories]);
    } elseif ($action === 'load_in') {
        $merge_file = "words/merged_4.csv";
        $file = fopen($merge_file, "r");
        $data = fgetcsv($file);
        while (($data = fgetcsv($file)) !== FALSE) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO words (category_id, word) VALUES (?, ?)");
            $stmt->execute([$data[0], $data[1]]);
        }
        fclose($file);
        echo json_encode(['status' => 'success', 'message' => 'Words loaded successfully']);
    } elseif ($action === 'load_out') {
        $stmt = $pdo->prepare("SELECT DISTINCT category_id, category_name FROM categories ORDER BY category_id");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $category_list = array_column($categories, 'category_name');
        $id_lists = array_column($categories, 'category_id');

        // Ensure the 'categories' directory exists
        if (!is_dir('categories')) {
            mkdir('categories', 0777, true);
        }

        for ($i = 0; $i < count($category_list); $i++) {
            $stmt = $pdo->prepare("SELECT * FROM words WHERE category_id = ?");
            $stmt->execute([$id_lists[$i]]);
            $words = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $word_list = array_column($words, 'word');

            $file_path = "categories/" . $category_list[$i] . ".json";
            $file = fopen($file_path, "w");
            if ($file) {
                fwrite($file, json_encode($word_list, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                fclose($file);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to open file: ' . $file_path]);
                return;
            }
        }
        echo json_encode(['status' => 'success', 'message' => 'Words exported successfully']);
    } elseif ($action === 'submit_letter') {
        $game_id = $_POST['game_id'];
        $letter = $_POST['letter'];
        $speller = $_POST['player'];

        $stmt = $pdo->prepare("SELECT * from games WHERE games.game_id = ?");
        $stmt->execute([$game_id]);
        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $current_word = $details[0]['current_word'] . $letter;

        $completion = $_POST['completion'] == 'true' ? 1 : 0;
        if ($details[0]['status'] === 'ongoing') {
            if ($completion === 1) {
                $stmt = $pdo->prepare("UPDATE games SET current_word = ? WHERE games.game_id = ?");
                $stmt->execute(['', $game_id]);
                $stmt = $pdo->prepare("INSERT INTO words_used(game_id, word, last_guesser) VALUES (?, ?, ?);");
                $stmt->execute([$game_id, $current_word, $speller]);

                echo json_encode(['status' => 'success', 'message' => 'Word completed']);
            } else {
                $stmt = $pdo->prepare("UPDATE games SET current_word = ? WHERE games.game_id = ?");
                $stmt->execute([$current_word, $game_id]);

                echo json_encode(['status' => 'success', 'message' => 'Letter added']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Game has ended']);
        }
    }
}
