# Africa Broadcasting Academy (ABA) Website

Welcome to the official repository for the **Africa Broadcasting Academy (ABA)** website. This platform acts as the digital gateway for empowering young African storytellers through hands-on broadcast training, media literacy, and authentic narrative reclamation.

## 🚀 Project Overview

The Africa Broadcasting Academy is dedicated to unleashing authentic narratives through broadcast excellence. This website serves to:
- Showcase the academy's mission, values, and success stories.
- Provide detailed information on training programs and student projects.
- Facilitate student enrollment through a comprehensive online application system.
- Connect with aspiring media professionals across Africa.

## 🛠 Tech Stack

**Frontend:**
*   **HTML5 & CSS3:** Semantic markup and custom styling using modern CSS variables.
*   **Bootstrap 5:** Responsive layout and component library.
*   **JavaScript:** Interactive elements and animations.
*   **Libraries:**
    *   [AOS (Animate On Scroll)](https://michalsnik.github.io/aos/) for scroll animations.
    *   [Three.js](https://threejs.org/) for 3D visual effects.
    *   [Lucide Icons](https://lucide.dev/) & [Font Awesome](https://fontawesome.com/) for iconography.

**Backend:**
*   **PHP:** Server-side logic for handling forms and application processing.
*   **PHPMailer:** Robust library for sending transactional emails (applications and confirmations).

## 📂 Project Structure

```bash
bta-website/
├── assets/          # Images, logos, and static media
├── css/             # Custom stylesheets (style.css, etc.)
├── js/              # JavaScript files
├── PHPMailer/       # Email sending library
├── about.html       # About Us page
├── application.php  # Backend logic for processing applications
├── applicationform.html # Student application form
├── contact.html     # Contact information page
├── index.html       # Homepage
├── programs.html    # Academy training programs
├── stories.html     # Success stories and articles
├── students.html    # Showcase of student projects
└── thank-you.html   # Application success page
```

## ⚙️ Setup & Installation

To run this project locally, you will need a PHP-enabled web server (e.g., MAMP, XAMPP, or Apache).

1.  **Clone the Repository:**
    ```bash
    git clone https://github.com/your-username/bta-website.git
    cd bta-website
    ```

2.  **Move to Server Directory:**
    *   **MAMP (macOS):** Move the folder to `/Applications/MAMP/htdocs/`.
    *   **XAMPP (Windows):** Move the folder to `C:\xampp\htdocs\`.

3.  **Configure Email Settings:**
    *   Open `application.php`.
    *   Update the `SMTP_HOST`, `SMTP_USER`, and `SMTP_PASS` constants with your mail server credentials to enable the application form.

4.  **Start the Server:**
    *   Launch MAMP/XAMPP and start the Apache server.
    *   Open your browser and navigate to `http://localhost:8888/bta-website/index.html` (port may vary).

## 📝 Features

*   **Dynamic Hero Section:** Immersive entry with glassmorphism effects and animations.
*   **Application Portal:** Secure PHP-based form (`applicationform.html`) that captures student details, processes data, and sends automated email confirmations via `application.php`.
*   **Responsive Design:** Fully optimized for mobile, tablet, and desktop viewing.
*   **Media Showcase:** Galleries and sections dedicated to student work and academy training facilities.

## 🤝 Contributing

Contributions are welcome! Please follow these steps:
1.  Fork the repository.
2.  Create a new branch (`git checkout -b feature/AmazingFeature`).
3.  Commit your changes (`git commit -m 'Add some AmazingFeature'`).
4.  Push to the branch (`git push origin feature/AmazingFeature`).
5.  Open a Pull Request.

## 👥 Credits

Developed significantly by **Vincent Agber**.

## 📄 License

This project is proprietary to the **Africa Broadcasting Academy**. All rights reserved.
