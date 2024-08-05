CREATE DATABASE GameVault_db;

USE GameVault_db;

CREATE TABLE IF NOT EXISTS Usuarios (
idUsuario INT NOT NULL AUTO_INCREMENT,
correo VARCHAR(255) NOT NULL,
nombreUsuario VARCHAR(30) NOT NULL,
contrasenaHash VARCHAR(255) NOT NULL,
adm BOOLEAN DEFAULT FALSE,
PRIMARY KEY(idUsuario)
);

CREATE TABLE IF NOT EXISTS Perfiles(
idPerfil INT NOT NULL AUTO_INCREMENT,
idUsuario INT NOT NULL,
nombrePerfil VARCHAR(30),
fotoPerfil VARCHAR(255) DEFAULT "img/default.png",
descripcion VARCHAR(255) DEFAULT "Hola, soy nuevo!",
PRIMARY KEY (idPerfil),
FOREIGN KEY (idUsuario) REFERENCES Usuarios(idUsuario)
);

CREATE TABLE IF NOT EXISTS Juegos(
idJuego INT NOT NULL AUTO_INCREMENT,
titulo VARCHAR(100) NOT NULL,
genero VARCHAR(100) NOT NULL,
plataformas VARCHAR(100) NOT NULL,
desarrollador VARCHAR(100) NOT NULL,
fechaLanzamiento DATE NOT NULL,
descripcion TEXT NOT NULL,
fotoJuego VARCHAR(255) NOT NULL,
promedio FLOAT NOT NULL DEFAULT 0,
PRIMARY KEY (idJuego)
);

CREATE TABLE IF NOT EXISTS BibliotecaPersonal(
idBibliotecaPersonal INT NOT NULL AUTO_INCREMENT,
idUsuario INT NOT NULL,
idJuego INT NOT NULL,
estadoJuego TINYINT DEFAULT 1 NOT NULL,
fechaAdicion DATE NOT NULL,
PRIMARY KEY (idBibliotecaPersonal),
FOREIGN KEY (idUsuario) REFERENCES Usuarios(idUsuario),
FOREIGN KEY (idJuego) REFERENCES Juegos(idJuego)
);

CREATE TABLE IF NOT EXISTS Resenas(
idResena INT NOT NULL AUTO_INCREMENT,
idUsuario INT NOT NULL,
idJuego INT NOT NULL,
calificacion TINYINT NOT NULL,
comentario TEXT,
fechaPublicacion DATE,
PRIMARY KEY (idResena),
FOREIGN KEY (idUsuario) REFERENCES Usuarios(idUsuario),
FOREIGN KEY (idJuego) REFERENCES Juegos(idJuego)
);

-- INSERTAR --

DELIMITER $$
DROP PROCEDURE IF EXISTS InsertarUsuario $$
CREATE PROCEDURE InsertarUsuario(p_correo VARCHAR(255), p_nombreUsuario VARCHAR(255), p_contrasenaHash VARCHAR(255))
BEGIN
	INSERT INTO Usuarios (correo, nombreUsuario, contrasenaHash)
    VALUES (p_correo, p_nombreUsuario, p_contrasenaHash);
END $$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS InsertarPerfil $$
CREATE PROCEDURE InsertarPerfil(p_idUsuario INT, p_nombrePerfil VARCHAR(30), p_fotoPerfil VARCHAR(255), p_descripcion VARCHAR(255))
BEGIN
    INSERT INTO Perfiles (idUsuario, nombrePerfil, fotoPerfil, descripcion)
    VALUES (p_idUsuario, p_nombrePerfil, p_fotoPerfil, p_descripcion);
END $$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS InsertarJuego $$
CREATE PROCEDURE InsertarJuego(p_titulo VARCHAR(100), p_genero VARCHAR(100), p_plataformas VARCHAR(100),
p_desarrollador VARCHAR(100), p_fechaLanzamiento DATE, p_descripcion TEXT, p_fotoJuego VARCHAR(255))
BEGIN
    INSERT INTO Juegos (titulo, genero, plataformas, desarrollador, fechaLanzamiento, descripcion, fotoJuego)
    VALUES (p_titulo, p_genero, p_plataformas, p_desarrollador, p_fechaLanzamiento, p_descripcion, p_fotoJuego);
END $$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS InsertarBibliotecaPersonal $$
CREATE PROCEDURE InsertarBibliotecaPersonal(p_idUsuario INT, p_idJuego INT)
BEGIN
    INSERT INTO BibliotecaPersonal (idUsuario, idJuego, fechaAdicion)
    VALUES (p_idUsuario, p_idJuego, CURRENT_TIMESTAMP);
END $$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS InsertarResena $$
CREATE PROCEDURE InsertarResena(p_idUsuario INT, p_idJuego INT, p_calificacion TINYINT, p_comentario TEXT)
BEGIN
    INSERT INTO Resenas (idUsuario, idJuego, calificacion, comentario, fechaPublicacion)
    VALUES (p_idUsuario, p_idJuego, p_calificacion, p_comentario, CURRENT_TIMESTAMP);
END $$
DELIMITER ;

-- ACTUALIZAR --

DELIMITER $$
DROP PROCEDURE IF EXISTS ActualizarUsuario $$
CREATE PROCEDURE ActualizarUsuario(p_idUsuario INT, p_correo VARCHAR(255), p_nombreUsuario VARCHAR(255))
BEGIN
	UPDATE Usuarios
    SET correo = p_correo, nombreUsuario = p_nombreUsuario
    WHERE idUsuario = p_idUsuario;
END $$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS ActualizarPerfil $$
CREATE PROCEDURE ActualizarPerfil(p_idPerfil INT, p_nombrePerfil VARCHAR(30), p_descripcion VARCHAR(255))
BEGIN
    UPDATE Perfiles
    SET nombrePerfil = p_nombrePerfil, descripcion = p_descripcion
    WHERE idPerfil = p_idPerfil;
END $$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS ActualizarJuego $$
CREATE PROCEDURE ActualizarJuego(p_idJuego INT, p_titulo VARCHAR(100), p_genero VARCHAR(100), p_plataformas VARCHAR(100),
p_desarrollador VARCHAR(100), p_fechaLanzamiento DATE, p_descripcion TEXT)
BEGIN
    UPDATE Juegos
    SET titulo = p_titulo, genero = p_genero, plataformas = p_plataformas,
    desarrollador = p_desarrollador, fechaLanzamiento = p_fechaLanzamiento,
    descripcion = p_descripcion
    WHERE idJuego = p_idJuego;
END $$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS ActualizarBibliotecaPersonal $$
CREATE PROCEDURE ActualizarBibliotecaPersonal(p_idBibliotecaPersonal INT, p_estadoJuego TINYINT)
BEGIN
    UPDATE BibliotecaPersonal
    SET estadoJuego = p_estadoJuego
    WHERE idBibliotecaPersonal = p_idBibliotecaPersonal;
END $$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS ActualizarResena $$
CREATE PROCEDURE ActualizarResena(p_idResena INT, p_calificacion TINYINT, p_comentario TEXT)
BEGIN
    UPDATE Resenas
    SET calificacion = p_calificacion, comentario = p_comentario, fechaPublicacion = CURRENT_TIMESTAMP
    WHERE idResena = p_idResena;
END $$
DELIMITER ;

-- ELIMINAR --

DELIMITER $$
DROP PROCEDURE IF EXISTS EliminarUsuario $$
CREATE PROCEDURE EliminarUsuario(p_idUsuario INT)
BEGIN
	DELETE FROM Usuarios
    WHERE idUsuario = p_idUsuario;
END $$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS EliminarPerfil $$
CREATE PROCEDURE EliminarPerfil(p_idPerfil INT)
BEGIN
    DELETE FROM Perfiles
    WHERE idPerfil = p_idPerfil;
END $$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS EliminarJuego $$
CREATE PROCEDURE EliminarJuego(p_idJuego INT)
BEGIN
    DELETE FROM Juegos
    WHERE idJuego = p_idJuego;
END $$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS EliminarBibliotecaPersonal $$
CREATE PROCEDURE EliminarBibliotecaPersonal(p_idBibliotecaPersonal INT)
BEGIN
    DELETE FROM BibliotecaPersonal
    WHERE idBibliotecaPersonal = p_idBibliotecaPersonal;
END $$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS EliminarResena $$
CREATE PROCEDURE EliminarResena(p_idResena INT)
BEGIN
    DELETE FROM Resenas
    WHERE idResena = p_idResena;
END $$
DELIMITER ;

-- CONSULTAR --

DELIMITER $$
DROP PROCEDURE IF EXISTS ConsultarUsuario $$
CREATE PROCEDURE ConsultarUsuario(p_correo INT)
BEGIN
	SELECT * FROM Usuarios WHERE correo = p_correo;
END $$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS ConsultarPerfil $$
CREATE PROCEDURE ConsultarPerfil(p_idPerfil INT)
BEGIN
    SELECT * FROM Perfiles WHERE idPerfil = p_idPerfil;
END $$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS ConsultarJuego $$
CREATE PROCEDURE ConsultarJuego(p_idJuego VARCHAR(100))
BEGIN
    SELECT * FROM Juegos WHERE idJuego = p_idJuego;
END $$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS ConsultarBibliotecaPersonal $$
CREATE PROCEDURE ConsultarBibliotecaPersonal(p_idUsuario INT, p_offset INT, p_limit INT)
BEGIN
    SELECT BP.idBibliotecaPersonal, J.idJuego, J.titulo, J.fotoJuego, BP.estadoJuego, BP.fechaAdicion FROM BibliotecaPersonal BP
    JOIN Juegos J USING (idJuego) WHERE BP.idUsuario = p_idUsuario ORDER BY J.titulo LIMIT p_offset, p_limit;
END $$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS ConsultarResena $$
CREATE PROCEDURE ConsultarResena(p_idJuego INT)
BEGIN
    SELECT * FROM Resenas WHERE idJuego = p_idJuego;
END $$
DELIMITER ;

