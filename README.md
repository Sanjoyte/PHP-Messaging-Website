# PHP-Messaging-Website
A simple WhatsApp-like messaging website built with PHP and MySQL. Users can sign up, log in, and exchange encrypted messages through a clean web interface. Conversations are sorted by the latest message, with unread chats shown in bold until opened.

🚀 Features

  🔐 User Authentication – Signup & Login system with hashed passwords.
  👥 Role Management – Separate Admin and Student roles (extendable).
  💬 Messaging System
    Send and receive messages between users.
    Conversations sorted by latest message.
    Unread messages are bold until opened.
    Messages marked as seen once read.
  🔒 Encryption – Messages stored encrypted in the database using AES.
  📜 Conversation View – Displays full chat history between two users.
  📱 Dashboard – Role-based options (e.g., Admin can create events).

🛠️ Tech Stack

Backend: PHP (procedural, mysqli)
Database: MySQL (XAMPP / phpMyAdmin)
Frontend: HTML + CSS (basic, can be styled further)
Server: Apache (XAMPP / LAMP stack)

📂 Project Structure
homepage.php      # Landing page (Signup / Login)  
signup.php        # User registration (assign role)  
login.php         # User login with sessions  
user.php          # Dashboard with role-based options  
messages.php      # Conversation list + create message  
conversation.php  # Chat view between two users  
connection.php    # Database connection file  
project.sql       # Database schema (User, Admin, Student, Messages)  

⚡ Setup Instructions

Clone the repo and place files in your XAMPP htdocs/ directory.
Import project.sql into phpMyAdmin to set up the database.
Update connection.php with your DB credentials.
Start Apache & MySQL from XAMPP.
Open http://localhost/<your-folder>/homepage.php in your browser.

🌟 Future Improvements

Add real-time updates with AJAX or WebSockets.
Improve UI with CSS frameworks (Bootstrap/Tailwind).
Implement file sharing (images, docs).
Push notifications for new messages.
