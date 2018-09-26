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
/*
CREATE TABLE Administrador
(
id_administrador integer primary key,
id_usuario integer,
foreign key(id_usuario) references Participante(id_participante)
);

CREATE TABLE Monitor
(
id_monitor integer primary key,
id_usuario integer,
foreign key(id_usuario) references Participante(id_participante)
);

CREATE TABLE Atividade
(
id_atividade integer primary key,
nome varchar(50) NOT NULL,
descricao varchar(255),
tipo varchar(20) NOT NULL, #Exemplo: Oficina, Minicurso, Palestra, Sala Temática
capacidade integer NOT NULL, #Exemplo: Em um torneio de truco com até 16 duplas participantes a capacidade seria 16
carga_horaria integer,
quantidade_sessoes integer #Exemplo: Uma oficina de Arduino que repetiu 2 vezes cada dia da semana teve 10 sessões
);

CREATE TABLE AdministradorAtividade
(
id_administrador integer,
id_atividade integer,
foreign key(id_administrador) references Administrador(id_administrador),
foreign key(id_atividade) references Atividade(id_atividade),
primary key(id_administrador, id_atividade)
);

CREATE TABLE MonitorAtividade
(
id_monitor integer,
id_atividade integer,
foreign key(id_monitor) references Monitor(id_monitor),
foreign key(id_atividade) references Atividade(id_atividade),
primary key(id_monitor, id_atividade)
);

*/