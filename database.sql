-- Create database
CREATE DATABASE IF NOT EXISTS church_networks;
USE church_networks;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'network_admin', 'church_admin', 'member') NOT NULL,
    status ENUM('active', 'inactive', 'pending') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Networks table
CREATE TABLE networks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    vision TEXT,
    mission TEXT,
    values_text TEXT,
    location VARCHAR(100) NOT NULL,
    country VARCHAR(50) NOT NULL,
    admin_id INT,
    logo_url VARCHAR(255),
    cover_image_url VARCHAR(255),
    website VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

-- Churches table
CREATE TABLE churches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    network_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    vision TEXT,
    mission TEXT,
    values_text TEXT,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    country VARCHAR(50) NOT NULL,
    pastor_name VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    website VARCHAR(255),
    logo_url VARCHAR(255),
    cover_image_url VARCHAR(255),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (network_id) REFERENCES networks(id)
);

-- Church Administrators table
CREATE TABLE church_admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    church_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (church_id) REFERENCES churches(id),
    UNIQUE KEY unique_church_admin (user_id, church_id)
);

-- Events table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    church_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    location TEXT,
    type ENUM('service', 'conference', 'workshop', 'youth', 'other') NOT NULL,
    image_url VARCHAR(255),
    status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') NOT NULL DEFAULT 'upcoming',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (church_id) REFERENCES churches(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Event Registrations table
CREATE TABLE event_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('registered', 'attended', 'cancelled') NOT NULL DEFAULT 'registered',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_event_registration (event_id, user_id)
);

-- Posts table
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    church_id INT NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    featured_image_url VARCHAR(255),
    status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (church_id) REFERENCES churches(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Post Images table
CREATE TABLE post_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    caption VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id)
);

-- Comments table
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

<<<<<<< HEAD
-- Likes table
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES posts(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

=======
>>>>>>> bf89c42 (Sandbox snapshot commit)
-- Media Library table
CREATE TABLE media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    church_id INT NOT NULL,
    user_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_type ENUM('image', 'video', 'document', 'other') NOT NULL,
    mime_type VARCHAR(100),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (church_id) REFERENCES churches(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Sample Data

-- Insert sample users
INSERT INTO users (username, password, email, full_name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@reddeiglesias.org', 'System Administrator', 'admin'),
('redvida_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@redvida.org', 'Red Vida Admin', 'network_admin'),
('esperanza_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@esperanzaviva.org', 'Red Esperanza Admin', 'network_admin'),
('nuevagen_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@nuevageneracion.org', 'Red Nueva Generación Admin', 'network_admin'),
('member1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member1@example.com', 'Miembro Uno', 'member');

-- Insert sample networks
INSERT INTO networks (name, description, vision, mission, location, country, admin_id, website) VALUES
('Red Vida Abundante', 'Transformando vidas a través del amor de Cristo', 'Ser una red de iglesias que impacte a América Latina', 'Conectar y equipar iglesias para transformar vidas', 'Ciudad de México', 'México', 2, 'www.redvida.org'),
('Red Esperanza Viva', 'Llevando esperanza a cada corazón', 'Ser un faro de esperanza en cada ciudad', 'Establecer iglesias saludables que transformen comunidades', 'Bogotá', 'Colombia', 3, 'www.esperanzaviva.org'),
('Red Nueva Generación', 'Formando la próxima generación de líderes', 'Levantar una generación que impacte al mundo', 'Formar líderes comprometidos con la excelencia', 'Buenos Aires', 'Argentina', 4, 'www.nuevageneracion.org');

-- Insert sample churches
INSERT INTO churches (network_id, name, description, vision, mission, address, city, country, pastor_name, phone, email) VALUES
(1, 'Iglesia Vida Abundante Central', 'Sede principal de la Red Vida Abundante', 'Ser una iglesia que transforme la ciudad', 'Alcanzar, formar y enviar', 'Av. Reforma 123', 'Ciudad de México', 'México', 'Juan Pérez', '+52 55 1234 5678', 'contacto@vidaabundante.org'),
(2, 'Iglesia Esperanza Viva Principal', 'Sede principal de la Red Esperanza Viva', 'Ser un centro de avivamiento', 'Restaurar vidas y familias', 'Calle 82 #12-34', 'Bogotá', 'Colombia', 'Carlos Rodríguez', '+57 1 234 5678', 'contacto@esperanzaviva.org'),
(3, 'Iglesia Nueva Generación Central', 'Sede principal de Red Nueva Generación', 'Impactar a la próxima generación', 'Formar líderes de excelencia', 'Av. 9 de Julio 1234', 'Buenos Aires', 'Argentina', 'Miguel Ángel López', '+54 11 2345 6789', 'contacto@nuevageneracion.org');

-- Insert sample events
INSERT INTO events (church_id, title, description, start_date, end_date, location, type, created_by) VALUES
(1, 'Conferencia de Jóvenes', 'Conferencia anual para jóvenes', '2024-03-15 18:00:00', '2024-03-15 21:00:00', 'Auditorio Principal', 'youth', 1),
(2, 'Escuela Bíblica', 'Inicio de nuevo ciclo de estudios', '2024-03-20 09:00:00', '2024-03-20 12:00:00', 'Salón de Estudios', 'workshop', 2),
(3, 'Retiro Familiar', 'Tiempo especial para familias', '2024-03-25 08:00:00', '2024-03-27 18:00:00', 'Centro de Retiros', 'conference', 3);

-- Insert sample posts
INSERT INTO posts (church_id, user_id, title, content, status) VALUES
(1, 2, 'Servicio Dominical', '¡Gracias a todos por asistir al servicio dominical! Fue un tiempo maravilloso en la presencia de Dios.', 'published'),
(2, 3, 'Próxima Reunión de Jóvenes', '¡No te pierdas nuestra próxima reunión de jóvenes! Tendremos música, juegos y un mensaje especial.', 'published'),
(3, 4, 'Testimonios de Sanidad', 'Compartiendo los testimonios de sanidad del último servicio de oración.', 'published');

-- Insert sample comments
INSERT INTO comments (post_id, user_id, content, status) VALUES
(1, 5, '¡Excelente mensaje! Muy edificante.', 'approved'),
(2, 5, 'Estaré allí con todo el grupo de jóvenes.', 'approved'),
(3, 5, '¡Gloria a Dios por su poder sanador!', 'approved');
