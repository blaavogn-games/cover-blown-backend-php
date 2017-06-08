DROP TABLE IF EXISTS player;
DROP TABLE IF EXISTS game;
DROP TABLE IF EXISTS deck;
DROP TABLE IF EXISTS msg;

CREATE TABLE player (
	id INT PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(30),
	IRLname VARCHAR(30),
	lastInfo TINYINT,
	discovered TINYINT,
	joined TINYINT
);

CREATE TABLE game (
	id INT PRIMARY KEY AUTO_INCREMENT,
	weapon VARCHAR(30), 
	murder VARCHAR(30), 
	round INT, 
	timeLimit BIGINT, 
	murderId INT,
	player0 INT,
	player1 INT,
	player2 INT,
	player3 INT,
	FOREIGN KEY (player0) REFERENCES player(id),
	FOREIGN KEY (player1) REFERENCES player(id),
	FOREIGN KEY (player2) REFERENCES player(id),
	FOREIGN KEY (player3) REFERENCES player(id)
);

CREATE TABLE deck (
	id INT PRIMARY KEY AUTO_INCREMENT,
	info1 VARCHAR(30), 
	info2 VARCHAR(30), 
	info3 VARCHAR(30), 
	type INT, 
	game_id INT,
	FOREIGN KEY (game_id) REFERENCES game(id)
);

CREATE TABLE msg (
	msg VARCHAR(255), 
	player INT, 
	type INT, 
	game_id INT,
	FOREIGN KEY (game_id) REFERENCES game(id)
);
