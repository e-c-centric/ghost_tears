# Ghosttears Game

## Project Overview

Ghosttears is a turn-based word-guessing game where the objective is to ensure that your opponent ends up completing a word. This project implements both single-player and multiplayer modes, allowing users to select from various word categories such as countries, rivers, and cities. The game is built using HTML, CSS, PHP, and AJAX to ensure a smooth and interactive experience.

### Features
- **Single-Player Mode**: Play against the system in a dynamic and challenging environment.
- **Multiplayer Mode**: Compete with friends, with the option to have more than two players in a game.
- **Category Selection**: Choose a category (e.g., countries, rivers, cities) to tailor the game’s word choices.
- **Real-Time Updates**: Enjoy a seamless gaming experience with real-time game state updates via AJAX.

---

## Project Structure

The project is organized as follows:

```
ghosttears/
│
├── index.html          # Main HTML file (Frontend UI)
├── style.css           # Styles for the game (CSS)
├── script.js           # Frontend logic, including AJAX calls
├── api.php             # Backend logic (PHP) to handle game requests
├── README.md           # Project documentation
└── categories/         # JSON files for different word categories
    ├── countries.json
    ├── rivers.json
    └── cities.json
```

### Frontend Files

- **index.html**: This is the main entry point of the application. It contains the structure of the game, including input fields, buttons, and displays for the game state.
  
- **style.css**: Contains all the styling rules for the application, making sure the user interface is visually appealing and responsive.

- **script.js**: Implements the client-side logic of the game. It handles user interactions, sends AJAX requests to the backend, and updates the game state on the frontend.

### Backend Files

- **api.php**: This file handles all backend logic, including starting a new game, validating user inputs, managing turns, and fetching the game state. It communicates with the database to store and retrieve game data.

### Data Files

- **categories/**: This folder contains JSON files, each representing a different category of words. These files are loaded when a game starts, reducing the need for frequent database queries during gameplay.

---

## Database Structure

The database is designed to ensure normalization and efficient querying. Below is the structure:

### Tables

1. **Categories**
   - `category_id` (Primary Key): Unique identifier for each category.
   - `category_name`: The name of the category.

2. **Words**
   - `category_id` (Foreign Key): Links to the `Categories` table.
   - `word` (Primary Key, Unique): The word itself.

3. **Users**
   - `user_id` (Primary Key): Unique identifier for each player.
   - `username`: Player’s username.
   - `password`: Hashed password for authentication.

4. **Games**
   - `game_id` (Primary Key): Unique identifier for each game session.
   - `category_id` (Foreign Key): Links to the `Categories` table to identify the category being played.
   - `status`: The current status of the game (e.g., ongoing, completed).
   - `current_word`: The current word formed in the game.

5. **Player_Game**
   - `game_id` (Foreign Key): Links to the `Games` table.
   - `user_id` (Foreign Key): Links to the `Users` table.
   - `turn_order`: Indicates the turn order of the player in the game.

6. **Words_Used**
   - `game_id` (Foreign Key): Links to the `Games` table.
   - `word`: Word that has already been used.

---

## API Endpoints

### `api.php`
- **Start Game (Single/Multiplayer)**
  - **Endpoint**: `POST /api.php`
  - **Action**: Initializes a new game session with the selected category.
  - **Parameters**: `category`, `mode`
  
- **Submit Letter**
  - **Endpoint**: `POST /api.php`
  - **Action**: Submits a letter and validates it against the current game state.
  - **Parameters**: `game_id`, `letter`
  
- **Fetch Game State**
  - **Endpoint**: `POST /api.php`
  - **Action**: Fetches the current state of the game.
  - **Parameters**: `game_id`

---

## Installation and Setup

### Prerequisites
- **PHP 7.x or higher**
- **MySQL 5.x or higher**
- **Web Server (Apache, Nginx, etc.)**

### Setup

1. **Clone the repository**:
   ```bash
   git clone https://github.com/e-c-centric/ghost_tears.git
   cd ghosttears
   ```

2. **Set up the database**:
   - Create a MySQL database and import the provided schema.
   - Update the database connection settings in `api.php`.

3. **Start the server**:
   - Place the project in your web server's root directory.
   - Ensure that PHP is configured correctly.

4. **Access the application**:
   - Open your browser and navigate to `http://localhost/ghosttears`.

---

## Future Enhancements

- **Real-Time Multiplayer with WebSockets**: Improve multiplayer experience by incorporating WebSockets for real-time interactions.
- **User Authentication**: Implement a more robust authentication system with features like account recovery.
- **Leaderboard**: Add a global leaderboard to track top players and their scores.
- **Mobile Compatibility**: Enhance the UI for better mobile experience.

---

## Contribution Guidelines

We welcome contributions! Please fork the repository, make your changes, and submit a pull request. Ensure that your code adheres to the project's coding standards.

---

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## Contact

For any questions, please contact the project maintainer at `egalezoyiku@gmail.com`.
