# --- File: app.py (CLEANED) ---

import mysql.connector
import requests
import json
from flask import Flask, request, jsonify
from flask_cors import CORS

# --- NLP Text Processing ---
from nltk.corpus import stopwords
from nltk.tokenize import word_tokenize
from collections import Counter

# --- 1. Initialize Your AI Server (Flask) ---
app = Flask(__name__)
CORS(app)

# --- 2. Database Configuration ---
DB_CONFIG = {
    'user': 'root',
    'password': '',
    'host': '127.0.0.1',
    'database': 'scientific_face'
}

# --- 3. The "AI" Helper Functions ---

def extract_keywords(abstract):
    """Simple NLP to find the most important words in the abstract."""
    stop_words = set(stopwords.words('english'))
    word_tokens = word_tokenize(abstract.lower())
    
    # Filter out stop words and short, non-alphabetic words
    filtered_words = [
        w for w in word_tokens
        if not w in stop_words and w.isalpha() and len(w) > 2
    ]
    
    # Return the 10 most common important words
    return [word for word, count in Counter(filtered_words).most_common(10)]

# --- PubMed API function ---
def get_novelty(keywords):
    """Calls the PubMed API to find similar papers."""
    print(f"Checking Novelty for: {keywords}")
    # search_term = "+".join(keywords) # Uncomment to use for real PubMed search later
    
    # Mock data structure to match your frontend's displayRsynResults expectation
    return [
        {
            "title": "A highly similar prior art paper",
            "similarity_score": 0.85,
            "summary": "This is a summary of a paper found on PubMed matching your keywords closely."
        },
        {
            "title": "Related work on one of your main topics",
            "similarity_score": 0.60,
            "summary": "This related work suggests your idea has some historical foundation."
        }
    ]

# --- Synergy Match function ---
def get_synergy_matches(keywords, user_id):
    """Queries your SQL database to find complementary users."""
    print(f"Checking Synergy for: {keywords}")
    matches = []
    
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        
        query_parts = []
        for kw in keywords:
            # Search for keywords in both 'skills' and 'topics' fields
            query_parts.append("skills LIKE %s OR topics LIKE %s")
        
        # NOTE: Multi-line string indentation is crucial in Python
        sql_query = f"""
SELECT user_id, username, email, skills, topics
FROM users
WHERE ({" OR ".join(query_parts)})
AND user_id != %s
LIMIT 5
"""
        
        sql_values = [f"%{kw}%" for kw in keywords for _ in (1,2)] + [user_id]
        
        cursor.execute(sql_query, tuple(sql_values))
        
        raw_matches = cursor.fetchall()
        
        # Format the output to match frontend structure (skills/topics as list)
        for match in raw_matches:
            # Simple keyword extraction (you can enhance this later)
            matches.append({
                "username": match['username'],
                "email": match['email'],
                # Using .split(', ') to handle comma-separated strings
                "matching_skills": match['skills'].split(', ') if match['skills'] else [],
                "matching_topics": match['topics'].split(', ') if match['topics'] else []
            })
        
        cursor.close()
        conn.close()
        
    except Exception as e:
        print(f"Database query error: {e}")

    return matches


# --- 4. The Main API Endpoint (CRITICAL FIX APPLIED) ---
# NOTE: The URL must match the frontend: /api/analyze-rsyn
@app.route('/api/analyze-rsyn', methods=['POST'])
def analyze_abstract():
    """
    This is the main function. It receives the abstract,
    runs all analyses, and returns the combined results.
    """
    if not request.is_json:
        return jsonify({"error": "Request must be JSON"}), 400
        
    data = request.json
    abstract = data.get('abstract')
    user_id = data.get('user_id')

    if not abstract or not user_id:
        return jsonify({"error": "Missing abstract or user_id"}), 400

    # Step A: Run NLP to create the "fingerprint"
    keywords = extract_keywords(abstract)
    
    # Step B: Run Novelty Assessment
    novelty_results = get_novelty(keywords)
    
    # Step C: Run Synergy Matching
    synergy_results = get_synergy_matches(keywords, user_id)
    
    # Step D: Return everything in one neat package
    # NOTE: Mapping the backend key names to the frontend key names (from search.html)
    return jsonify({
        "success": True,
        "keywords": keywords,         # frontend expects 'keywords'
        "prior_art": novelty_results, # frontend expects 'prior_art'
        "collaborators": synergy_results # frontend expects 'collaborators'
    })

# --- 5. Run the Server ---
if __name__ == '__main__':
    print("Starting R-SYN Engine API at http://127.0.0.1:5000 ...")
    app.run(debug=True, port=5000)

    # --- Root Index Route (Add this to app.py) ---
@app.route('/', methods=['GET'])
def index():
    # This route confirms the server is running and acts as a health check
    return jsonify({
        "status": "success",
        "message": "R-SYN Engine API is running. Use the /api/analyze-rsyn POST endpoint for analysis."
    })
# ---------------------------------------------