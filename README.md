SciFace: AI-Driven Research Collaboration Platform
SciFace is a full-stack web prototype designed to accelerate scientific discovery by breaking research silos. It moves beyond static social networking by using a custom R-SYN (Research Synergy & Novelty) Engine to help scientists validate their hypotheses and find optimal, complementary collaborators.

Status: This is a locally developed prototype (MVP) built using the XAMPP stack and a Python/Flask microservice.

🚀 Core Features
1. Novelty Assessment (External Validation)
The Problem: Manually searching for "Prior Art" is time-consuming and prone to human error.

The Solution: The engine utilizes Natural Language Processing (NLTK) to tokenize and filter user abstracts for key scientific terms. It then performs real-time queries via the PubMed E-utilities (Entrez) API.

The Output: Instantly displays the most relevant existing publications, allowing the researcher to assess the originality of their work before committing resources.

2. Synergy Matching (Internal Matchmaking)
The Problem: Effective collaboration requires complementary skills, not just similar ones.

The Solution: A custom matching algorithm that scans the internal MySQL database for "Research Fingerprints." Instead of matching two people with the same skill, it identifies gaps (e.g., matching a "Computational Biologist" with a "Wet-Lab Validation Expert").

The Output: Generates proactive "Collaboration Suggestions" based on interdisciplinary synergy scores.

🛠️ Technical Stack
Backend: Python (Flask), PHP (User session management)

Database: MySQL (Hosted via XAMPP)

AI/NLP: NLTK (Stop-word removal, keyword extraction)

Frontend: JavaScript (ES6+), HTML5, CSS3

API Integration: PubMed E-utilities API (Requests library)

📂 Project Structure
Plaintext

SciFace/
├── api/                  # Python Flask R-SYN Engine
│   ├── app.py            # Core AI logic & API endpoints
│   └── requirements.txt  # Python dependencies
├── web/                  # Frontend HTML/CSS/JS & PHP files
├── database/             # SQL exports for the user database
├── .gitignore            # Clean environment management
└── README.md             # Project documentation
🔧 Installation & Local Setup
To run this prototype in a local development environment:

Start XAMPP: Launch Apache and MySQL.

Database Setup: Import the .sql file in the /database folder into PHPMyAdmin.

Deploy Web Files: Place the /web folder content into your XAMPP htdocs directory.

Launch AI Engine:

Bash

cd api
pip install -r requirements.txt
python app.py
Access: Open http://localhost/sciface in your browser.

💡 Why SciFace?
In modern bioinformatics, data is only as valuable as its validation. SciFace was built with a "Dry-Lab/Wet-Lab" bridge mindset, ensuring that computational findings can find the experimental partners needed to turn data into published discoveries.

Developed by: Pratik Parlekar Focus: Full-Stack Bioinformatics & Research Tools
