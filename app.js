/**
 * SCIENTIFICFACE - Central JavaScript Integration File
 * This file provides shared functionality across all pages
 */

// ============================================================================
// CONFIGURATION & CONSTANTS
// ============================================================================

const API_CONFIG = {
    // UPDATED: Changed from '/api' to '' (empty string)
    // This tells the app to look for your PHP files in the *same folder*
    // as your HTML files, which is correct for your XAMPP setup.
    baseURL: '',
    endpoints: {
        login: 'login.php',
        register: 'register.php',
        followedFeed: 'get_feed.php', // Matched the PHP file
        search: 'search.php',
        submitArticle: 'submit_article.php',
        getProfile: 'get_profile.php',
        toggleFollow: 'toggle_follow.php',
        quickPost: 'quick_post.php' // Added this for home.html
    }
};

// Mock data flag - set to false when connecting to real backend
// UPDATED: This is the most important fix!
// This tells your app to use the *real* API service.
const USE_MOCK_DATA = false;

// ============================================================================
// AUTHENTICATION & SESSION MANAGEMENT
// ============================================================================

class AuthManager {
    constructor() {
        this.currentUser = this.loadUserFromStorage();
    }

    // Store user data in memory
    loadUserFromStorage() {
        // In production, this would check the PHP session.
        // For now, we'll assume not authenticated until login
        // UPDATED: Set to null so login is required
        return null;
    }

    async login(email, password) {
        if (USE_MOCK_DATA) {
            // This code will not run because USE_MOCK_DATA is false
            this.currentUser = {
                user_id: 1,
                full_name: 'Demo User',
                email: email,
                isAuthenticated: true
            };
            return { success: true, user: this.currentUser };
        }

        try {
            const response = await fetch(API_CONFIG.baseURL + API_CONFIG.endpoints.login, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });
            const data = await response.json();
            
            if (data.success) {
                // The PHP session is set by the server.
                // We just store the user info in the class for the frontend.
                this.currentUser = data.user;
                this.currentUser.isAuthenticated = true;
            }
            return data;
        } catch (error) {
            console.error('Login error:', error);
            return { success: false, error: 'Network error or invalid JSON response.' };
        }
    }

    async register(userData) {
        if (USE_MOCK_DATA) {
            // This code will not run
            return { success: true };
        }

        try {
            const response = await fetch(API_CONFIG.baseURL + API_CONFIG.endpoints.register, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(userData)
            });
            const data = await response.json();
            
            if (data.success) {
                // The PHP session is set by the server.
                this.currentUser = data.user;
                this.currentUser.isAuthenticated = true;
            }
            return data;
        } catch (error) {
            console.error('Registration error:', error);
            return { success: false, error: 'Network error or invalid JSON response.' };
        }
    }

    logout() {
        // In a real app, we would also call a 'logout.php' script
        // to destroy the session on the server.
        this.currentUser = null;
        window.location.href = 'index.html';
    }

    isAuthenticated() {
        // This is a basic check. A better way would be to ask the server
        // if the session is still valid.
        return this.currentUser && this.currentUser.isAuthenticated;
    }

    getCurrentUser() {
        return this.currentUser;
    }
}

// ============================================================================
// API SERVICE
// ============================================================================

class APIService {
    constructor() {
        // MockDataProvider is no longer needed if USE_MOCK_DATA is false
    }

    async getFollowedFeed(tab = 'following') {
        if (USE_MOCK_DATA) {
            return []; // Will not run
        }

        try {
            // Send the tab selection to the PHP script
            const response = await fetch(`${API_CONFIG.baseURL}${API_CONFIG.endpoints.followedFeed}?tab=${tab}`);
            return await response.json();
        } catch (error) {
            console.error('Error fetching feed:', error);
            throw error;
        }
    }

    async searchArticles(filters) {
        if (USE_MOCK_DATA) {
            return []; // Will not run
        }

        try {
            // UPDATED: Use URLSearchParams to properly format the query string for GET request
            const params = new URLSearchParams(filters);
            const response = await fetch(`${API_CONFIG.baseURL}${API_CONFIG.endpoints.search}?${params.toString()}`);
            return await response.json();
        } catch (error) {
            console.error('Error searching articles:', error);
            throw error;
        }
    }

    async submitArticle(articleData) {
        if (USE_MOCK_DATA) {
            return { success: false }; // Will not run
        }

        try {
            const response = await fetch(API_CONFIG.baseURL + API_CONFIG.endpoints.submitArticle, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(articleData)
            });
            return await response.json();
        } catch (error) {
            console.error('Error submitting article:', error);
            throw error;
        }
    }

    async getProfile(userId) {
        if (USE_MOCK_DATA) {
            return {}; // Will not run
        }

        try {
            const response = await fetch(`${API_CONFIG.baseURL}${API_CONFIG.endpoints.getProfile}?user=${userId}`);
            return await response.json();
        } catch (error) {
            console.error('Error fetching profile:', error);
            throw error;
        }
    }

    async toggleFollow(userId) {
        if (USE_MOCK_DATA) {
            return { success: false }; // Will not run
        }

        try {
            const response = await fetch(API_CONFIG.baseURL + API_CONFIG.endpoints.toggleFollow, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId })
            });
            return await response.json();
        } catch (error) {
            console.error('Error toggling follow:', error);
            throw error;
        }
    }
    
    // ADDED: The missing quickPost function
    async quickPost(postData) {
        if (USE_MOCK_DATA) {
            return { success: false }; // Will not run
        }

        try {
            const response = await fetch(API_CONFIG.baseURL + API_CONFIG.endpoints.quickPost, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(postData)
            });
            return await response.json();
        } catch (error) {
            console.error('Error submitting post:', error);
            throw error;
        }
    }
}

// ============================================================================
// MOCK DATA PROVIDER
// (This class is no longer used but kept for reference)
// ============================================================================

class MockDataProvider {
    // ... all mock data from your original file ...
    // ... all mock functions from your original file ...
}

// ============================================================================
// UI COMPONENTS & UTILITIES
// ============================================================================

class UIComponents {
    // Create a post card element
    static createPostCard(post) {
        const card = document.createElement('div');
        card.className = 'post-card';
        
        // UPDATED: Handle potentially missing data to prevent errors
        const noveltyScore = post.novelty_score || 0;
        const collaborators = post.suggested_collaborators || [];
        
        const noveltyPercent = Math.round(noveltyScore * 100);
        let noveltyClass = 'low';
        if (noveltyPercent >= 90) noveltyClass = 'high';
        else if (noveltyPercent >= 75) noveltyClass = 'medium';
        
        const collaboratorHTML = collaborators
            .map(collab => `<a href="profile.html?user=${collab.id}" class="collaborator-pill">${collab.name}</a>`)
            .join('');
        
        card.innerHTML = `
            <div class="post-header">
                <div>
                    <h3 class_name="post-title">${post.paper_title || 'Untitled Paper'}</h3>
                    <a href="profile.html?user=${post.author_id}" class="post-author">${post.authors_name || 'Unknown Author'}</a>
                </div>
            </div>
            
            <div class="post-meta">
                ${post.journal_name || 'No Journal'} • ${post.publication_years || 'N/A'}
            </div>
            
            <div class="post-details">
                <div class="detail-row">
                    <span class="detail-label">DOI:</span>
                    <span class="detail-value"><a href="httpsm://doi.org/${post.doi}" target="_blank">${post.doi || 'No DOI'}</a></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Source:</span>
                    <span class_name="detail-value"><a href="${post.link}" target="_blank">View Paper</a></span>
                </div>
            </div>
            
            ${collaborators.length > 0 ? `
            <div class="ai-section">
                <div class="novelty-score ${noveltyClass}">
                    🔬 Novelty Score: ${noveltyPercent}%
                </div>
                
                <div class="collaborators-section">
                    <h4>💡 Suggested Collaborators</h4>
                    <div class="collaborator-pills">
                        ${collaboratorHTML}
                    </div>
                </div>
            </div>
            ` : `
            <div class_name="novelty-score ${noveltyClass}" style="margin-top: 15px;">
                🔬 Novelty: ${noveltyPercent}%
            </div>
            `}
        `;
        
        return card;
    }

    // Show loading state
    static showLoading(container) {
        container.innerHTML = '<div class="loading">Loading...</div>';
    }

    // Show empty state
    static showEmptyState(container, title, message) {
        container.innerHTML = `
            <div class="empty-state">
                <h3>${title}</h3>
                <p>${message}</p>
            </div>
        `;
    }

    // Show error state
    static showError(container, message) {
        container.innerHTML = `
            <div class="empty-state">
                <h3>Error</h3>
                <p>${message}</p>
            </div>
        `;
    }

    // Show success message
    static showSuccess(message, duration = 3000) {
        const successDiv = document.createElement('div');
        successDiv.className = 'success-message';
        successDiv.textContent = message;
        successDiv.style.position = 'fixed';
        successDiv.style.top = '80px';
        successDiv.style.left = '50%';
        successDiv.style.transform = 'translateX(-50%)';
        successDiv.style.zIndex = '1000';
        successDiv.style.minWidth = '300px';
        
        document.body.appendChild(successDiv);
        
        setTimeout(() => {
            successDiv.remove();
        }, duration);
    }
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

const Utils = {
    // Get URL parameter
    getURLParameter(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    },

    // Validate email
    isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    // Format date
    formatDate(date) {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    },

    // Debounce function
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

// ============================================================================
// GLOBAL INITIALIZATION
// ============================================================================

// Initialize global instances
const authManager = new AuthManager();
const apiService = new APIService();

// Export to window for access in inline scripts
window.ScientificFace = {
    authManager,
    apiService,
    UIComponents,
    Utils,
    API_CONFIG
};

// Check authentication on page load (except for index.html)
document.addEventListener('DOMContentLoaded', () => {
    const currentPage = window.location.pathname.split('/').pop();
    
    // Skip auth check for login/register page
    if (currentPage !== 'index.html' && currentPage !== '') {
        // UPDATED: This auth check is commented out.
        // A real check would require an API call to the server
        // to see if the PHP session is valid. Otherwise,
        // authManager.isAuthenticated() will always be false on
        // a page reload, causing a redirect loop.
        /*
        if (!authManager.isAuthenticated()) {
            // Redirect to login if not authenticated
            window.location.href = 'index.html';
        }
        */
    }
});

// ============================================================================
// GLOBAL EVENT HANDLERS
// ============================================================================

// Handle logout clicks
document.addEventListener('click', (e) => {
    // Find the link that was clicked
    const link = e.target.closest('a');
    
    // UPDATED: Make the selector more specific to the "Logout" link
    if (link && link.getAttribute('href') === 'index.html' && link.textContent === 'Logout') {
        // Stop the link from navigating immediately
        e.preventDefault();
        
        // Use a simple modal/confirm box
        const confirmed = confirm('Are you sure you want to logout?');
        if (confirmed) {
            authManager.logout();
        }
    }
});

console.log('✅ SCIENTIFICFACE Application Loaded Successfully');

// Wait for the page to load
document.addEventListener("DOMContentLoaded", () => {

    const analyzeBtn = document.getElementById("analyze-btn");
    const abstractInput = document.getElementById("abstract-input");
    const userIdInput = document.getElementById("user-id-input");

    // Output Elements
    const resultsContainer = document.getElementById("results-container");
    const keywordsOutput = document.getElementById("keywords-output");
    const noveltyOutput = document.getElementById("novelty-output");
    const synergyOutput = document.getElementById("synergy-output");

    // Add click listener to the button
    analyzeBtn.addEventListener("click", async () => {

        const abstractText = abstractInput.value;
        const currentUserId = userIdInput.value;

        if (abstractText.trim() === "") {
            alert("Please paste your abstract first.");
            return;
        }

        analyzeBtn.disabled = true;
        analyzeBtn.textContent = "Analyzing... This may take a moment.";

        try {
            // This is the fetch call to your Python AI server!
            const response = await fetch("http://127.0.0.1:5000/analyze", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    "abstract": abstractText,
                    "user_id": currentUserId
                }),
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const results = await response.json();

            // --- Display the Results ---

            // Show keywords
            keywordsOutput.textContent = results.processed_keywords.join(", ");

            // Show novelty results
            noveltyOutput.innerHTML = ""; // Clear old results
            if (results.novelty.length > 0) {
                results.novelty.forEach(paper => {
                    const li = document.createElement("li");
                    li.innerHTML = `<a href="${paper.link}" target="_blank">${paper.title}</a>`;
                    noveltyOutput.appendChild(li);
                });
            } else {
                noveltyOutput.innerHTML = "<li>No strong matches found. Your idea may be highly novel!</li>";
            }

            // Show synergy results
            synergyOutput.innerHTML = ""; // Clear old results
            if (results.synergy.length > 0) {
                results.synergy.forEach(user => {
                    const tr = document.createElement("tr");
                    tr.innerHTML = `
                        <td>${user.username}</td>
                        <td>${user.email}</td>
                        <td>${user.skills || 'N/A'}</td>
                        <td>${user.topics || 'N/A'}</td>
                    `;
                    synergyOutput.appendChild(tr);
                });
            } else {
                synergyOutput.innerHTML = "<tr><td colspan='4'>No potential collaborators found on the platform yet.</td></tr>";
            }

            resultsContainer.style.display = "block";

        } catch (error) {
            console.error("Error analyzing abstract:", error);
            alert("An error occurred. Make sure the AI server is running.");
        } finally {
            // Re-enable the button
            analyzeBtn.disabled = false;
            analyzeBtn.textContent = "Analyze for Novelty & Synergy";
        }
    });
});