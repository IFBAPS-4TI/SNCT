DROP DATABASE IF EXISTS snct;
CREATE DATABASE IF NOT EXISTS snct;
USE snct;

CREATE TABLE Usuario
(
id_usuario integer primary key AUTO_INCREMENT,
nome varchar(255) NOT NULL,
email varchar(255) NOT NULL UNIQUE,
nascimento varchar(10) NOT NULL,
cpf varchar(11) NOT NULL UNIQUE,
senha text NOT NULL
);

CREATE TABLE Administradores
(
id_administrador integer AUTO_INCREMENT,
id_usuario integer,
PRIMARY KEY (id_administrador, id_usuario),
FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario)
ON DELETE CASCADE
);
CREATE TABLE Atividade
(
id_atividade integer primary key AUTO_INCREMENT,
nome varchar(50) NOT NULL UNIQUE,
descricao varchar(8000) NOT NULL,
tipo int NOT NULL, #Exemplo: Oficina, Minicurso, Palestra, Sala Temática
capacidade integer NOT NULL, #Exemplo: Em um torneio de truco com até 16 duplas participantes a capacidade seria 16
duracao integer NOT NULL
);

CREATE TABLE Monitor
(
id_monitor integer primary key AUTO_INCREMENT,
id_usuario integer,
id_atividade integer unique,
foreign key(id_usuario) references Participante(id_participante),
foreign key(id_atividade) references Atividade(id_atividade)
);

CREATE TABLE Sessoes
(
id_sessao integer primary key AUTO_INCREMENT,
id_atividade integer,
timestamp_ativ varchar(16) unique,
foreign key(id_atividade) references Atividade(id_atividade)
);