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
nome varchar(255) NOT NULL UNIQUE,
descricao varchar(8000) NOT NULL,
certificado int(1) NOT NULL,
tipo int(1) NOT NULL,
capacidade integer NOT NULL,
duracao integer NOT NULL
);

CREATE TABLE Monitor
(
id_monitor integer primary key AUTO_INCREMENT,
id_usuario integer,
id_atividade integer,
organizador int(1) NOT NULL,
foreign key(id_usuario) references Usuario(id_usuario) ON DELETE CASCADE,
foreign key(id_atividade) references Atividade(id_atividade) ON DELETE CASCADE
);

CREATE TABLE Sessoes
(
id_sessao integer primary key AUTO_INCREMENT,
id_atividade integer,
local_ativ varchar(255) NOT NULL,
timestamp_ativ varchar(16) NOT NULL,
foreign key(id_atividade) references Atividade(id_atividade) ON DELETE CASCADE
);

CREATE TABLE Inscricoes
(
  id_inscricao integer primary key auto_increment,
  id_usuario integer NOT NULL,
  id_sessao integer NOT NULL,
  compareceu int(1) NOT NULL,
  foreign key (id_usuario) references Usuario(id_usuario) on delete cascade,
  foreign key (id_sessao) references Sessoes(id_sessao) on delete cascade
);
