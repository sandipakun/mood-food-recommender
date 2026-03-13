/**
 * Mood-Based Recipe Recommender - Main JavaScript
 * Handles API calls, UI interactions, and state management
 */

// API Base URL
const APP_BASE_URL =
    (typeof window !== 'undefined' && window.APP_BASE_URL)
        ? window.APP_BASE_URL
        : (window.location.origin + '/mood-food-recommender');
const API_BASE = APP_BASE_URL.replace(/\/+$/, '') + '/api';

// State Management
const AppState = {
    currentMood: null,
    filters: {
        is_veg: null,
        is_high_protein: null,
        multi_cuisine: 0
    },
    suggestions: [],
    currentRecipe: null
};

// Initialize App with Premium Performance
document.addEventListener('DOMContentLoaded', function() {
    // Use requestIdleCallback for non-critical initialization
    if ('requestIdleCallback' in window) {
        requestIdleCallback(initializeApp, { timeout: 2000 });
    } else {
        // Fallback for browsers without requestIdleCallback
        setTimeout(initializeApp, 0);
    }
});

function initializeApp() {
    loadMoods();
    setupEventListeners();
    checkAuthStatus();
    
    // Restore state from sessionStorage
    restoreState();
}

// Restore state from sessionStorage
function restoreState() {
    const savedMood = sessionStorage.getItem('selectedMood');
    const savedRecipeId = sessionStorage.getItem('selectedRecipeId');
    
    if (savedMood) {
        AppState.currentMood = savedMood;
        // Mark the mood as selected in UI
        setTimeout(() => {
            document.querySelectorAll('.mood-card').forEach(card => {
                if (card.dataset.mood === savedMood) {
                    card.classList.add('selected');
                }
            });
            // Show filters and suggestions if mood was selected
            const filtersSection = document.getElementById('filters-section');
            const suggestionsSection = document.getElementById('suggestions-section');
            if (filtersSection) filtersSection.style.display = 'block';
            if (suggestionsSection) suggestionsSection.style.display = 'block';
            // Reload suggestions
            loadSuggestions();
        }, 500);
    }
}

// API Functions
async function apiCall(endpoint, options = {}) {
    try {
        const response = await fetch(`${API_BASE}/${endpoint}`, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'API request failed');
        }
        
        return data;
    } catch (error) {
        console.error('API Error:', error);
        showAlert('error', error.message || 'Something went wrong. Please try again.');
        throw error;
    }
}

// Load Moods
async function loadMoods() {
    try {
        const response = await apiCall('moods.php');
        const moods = response.data;
        
        renderMoods(moods);
    } catch (error) {
        console.error('Failed to load moods:', error);
    }
}

// Render Mood Selection with Premium Animations
function renderMoods(moods) {
    const container = document.getElementById('mood-container');
    if (!container) return;
    
    // Use DocumentFragment for better performance
    const fragment = document.createDocumentFragment();
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = moods.map((mood, index) => `
        <div class="mood-card" 
             data-mood="${mood.slug}" 
             role="button"
             tabindex="0"
             aria-label="Select ${mood.name} mood"
             onkeypress="handleMoodKeyPress(event, '${mood.slug}')"
             style="animation: fadeInUp 0.5s cubic-bezier(0.4, 0, 0.2, 1) ${index * 0.1}s both; opacity: 0;">
            <span class="mood-icon" aria-hidden="true">${mood.icon || '😊'}</span>
            <h3 class="mood-name">${escapeHtml(mood.name)}</h3>
        </div>
    `).join('');
    
    while (tempDiv.firstChild) {
        fragment.appendChild(tempDiv.firstChild);
    }
    
    container.innerHTML = '';
    container.appendChild(fragment);
    
    // Add click listeners with passive event listeners for better performance
    container.querySelectorAll('.mood-card').forEach(card => {
        card.addEventListener('click', function() {
            const moodSlug = this.dataset.mood;
            selectMood(moodSlug);
        }, { passive: true });
    });
}

// Handle Keyboard Navigation for Moods
function handleMoodKeyPress(event, moodSlug) {
    if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        selectMood(moodSlug);
    }
}

// Select Mood with Premium Animation
function selectMood(moodSlug) {
    AppState.currentMood = moodSlug;
    
    // Save to sessionStorage
    sessionStorage.setItem('selectedMood', moodSlug);
    
    // Update UI with smooth transition
    document.querySelectorAll('.mood-card').forEach(card => {
        card.classList.remove('selected');
        if (card.dataset.mood === moodSlug) {
            // Add premium selection animation
            card.classList.add('selected');
            // Trigger a subtle pulse animation
            card.style.animation = 'pulse 0.3s ease-out';
            setTimeout(() => {
                card.style.animation = '';
            }, 300);
        }
    });
    
    // Smooth scroll to suggestions if they exist
    const suggestionsSection = document.getElementById('suggestions-section');
    if (suggestionsSection && suggestionsSection.style.display !== 'none') {
        setTimeout(() => {
            suggestionsSection.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'nearest' 
            });
        }, 100);
    }
    
    // Load suggestions
    loadSuggestions();
}

// Load Suggestions
async function loadSuggestions() {
    if (!AppState.currentMood) {
        showAlert('info', 'Please select a mood first!');
        return;
    }
    
    const suggestionsContainer = document.getElementById('suggestions-container');
    if (suggestionsContainer) {
        suggestionsContainer.innerHTML = '<div class="loading"><div class="spinner"></div><p>Finding perfect recipes for you...</p></div>';
    }
    
    try {
        const params = new URLSearchParams({
            mood: AppState.currentMood
        });
        
        // Add filters
        if (AppState.filters.is_veg !== null) {
            params.append('is_veg', AppState.filters.is_veg);
        }
        if (AppState.filters.is_high_protein !== null) {
            params.append('is_high_protein', AppState.filters.is_high_protein);
        }
        if (AppState.filters.multi_cuisine) {
            params.append('multi_cuisine', AppState.filters.multi_cuisine);
        }
        
        const response = await apiCall(`suggestions.php?${params}`);
        AppState.suggestions = response.data.suggestions;
        
        renderSuggestions(AppState.suggestions);
        
        if (response.data.note) {
            showAlert('info', response.data.note);
        }
    } catch (error) {
        if (suggestionsContainer) {
            suggestionsContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">😔</div>
                    <p>Couldn't find recipes. Please try again!</p>
                </div>
            `;
        }
    }
}

// Render Suggestions with Premium Staggered Animation
function renderSuggestions(suggestions) {
    const container = document.getElementById('suggestions-container');
    if (!container) return;
    
    if (suggestions.length === 0) {
        container.innerHTML = `
            <div class="empty-state" style="animation: fadeInUp 0.5s ease-out;">
                <div class="empty-state-icon">🍽️</div>
                <p>No recipes found. Try adjusting your filters!</p>
            </div>
        `;
        return;
    }
    
    // Use DocumentFragment for better performance
    const fragment = document.createDocumentFragment();
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = `
        <div class="recipe-grid">
            ${suggestions.map((recipe, index) => `
                <div class="recipe-card" 
                     onclick="viewRecipe(${recipe.id})"
                     role="button"
                     tabindex="0"
                     aria-label="View recipe: ${escapeHtml(recipe.title)}"
                     onkeypress="handleRecipeKeyPress(event, ${recipe.id})"
                     style="animation: fadeInUp 0.5s cubic-bezier(0.4, 0, 0.2, 1) ${index * 0.1}s both; opacity: 0;">
                    <img src="${escapeHtml(recipe.image_url || 'https://via.placeholder.com/400x200?text=Recipe')}" 
                         alt="${escapeHtml(recipe.title)}"
                         class="recipe-image"
                         loading="lazy"
                         decoding="async"
                         width="400"
                         height="200"
                         onload="this.classList.add('loaded')"
                         onerror="this.onerror=null; this.src='https://via.placeholder.com/400x200?text=Recipe+Image'; this.classList.add('loaded');">
                    <div class="recipe-body">
                        <h3 class="recipe-title">${escapeHtml(recipe.title)}</h3>
                        <span class="recipe-cuisine">${recipe.cuisine_emoji || ''} ${escapeHtml(recipe.cuisine)}</span>
                        <div class="recipe-meta">
                            <span>⏱️ ${recipe.prep_time} min</span>
                            <span>🔥 ${recipe.calories} cal</span>
                        </div>
                        <div class="recipe-badges">
                            ${recipe.is_veg ? '<span class="badge badge-veg">🌱 Veg</span>' : ''}
                            ${recipe.is_high_protein ? '<span class="badge badge-protein">💪 High Protein</span>' : ''}
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    
    while (tempDiv.firstChild) {
        fragment.appendChild(tempDiv.firstChild);
    }
    
    container.innerHTML = '';
    container.appendChild(fragment);
}

// Handle Keyboard Navigation for Recipes
function handleRecipeKeyPress(event, recipeId) {
    if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        viewRecipe(recipeId);
    }
}

// View Recipe Detail
function viewRecipe(recipeId) {
    // Save recipe ID to sessionStorage
    sessionStorage.setItem('selectedRecipeId', recipeId);
    window.location.href = `recipe.php?id=${recipeId}`;
}

// Setup Event Listeners
function setupEventListeners() {
    // Diet type radio buttons (veg, non-veg, all)
    const dietTypeRadios = document.querySelectorAll('input[name="diet-type"]');
    dietTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'veg') {
                AppState.filters.is_veg = 1;
            } else if (this.value === 'nonveg') {
                AppState.filters.is_veg = 0;
            } else {
                AppState.filters.is_veg = null;
            }
            if (AppState.currentMood) {
                loadSuggestions();
            }
        });
    });
    
    // High protein checkbox
    const proteinCheckbox = document.getElementById('filter-high-protein');
    if (proteinCheckbox) {
        proteinCheckbox.addEventListener('change', function() {
            AppState.filters.is_high_protein = this.checked ? 1 : null;
            if (AppState.currentMood) {
                loadSuggestions();
            }
        });
    }
    
    // Multi-cuisine checkbox
    const multiCuisineCheckbox = document.getElementById('filter-multi-cuisine');
    if (multiCuisineCheckbox) {
        multiCuisineCheckbox.addEventListener('change', function() {
            AppState.filters.multi_cuisine = this.checked ? 1 : 0;
            if (AppState.currentMood) {
                loadSuggestions();
            }
        });
    }
    
    // Clear filters button
    const clearFiltersBtn = document.getElementById('clear-filters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            AppState.filters = { is_veg: null, is_high_protein: null, multi_cuisine: 0 };
            // Reset diet type to "all"
            const allDietRadio = document.getElementById('filter-all-diet');
            if (allDietRadio) allDietRadio.checked = true;
            if (proteinCheckbox) proteinCheckbox.checked = false;
            if (multiCuisineCheckbox) multiCuisineCheckbox.checked = false;
            if (AppState.currentMood) {
                loadSuggestions();
            }
        });
    }
}

// Load Recipe Detail
async function loadRecipeDetail(recipeId) {
    try {
        const response = await apiCall(`recipe.php?id=${recipeId}`);
        AppState.currentRecipe = response.data;
        renderRecipeDetail(response.data);
    } catch (error) {
        console.error('Failed to load recipe:', error);
    }
}

// Render Recipe Detail
function renderRecipeDetail(recipe) {
    const container = document.getElementById('recipe-detail-container');
    if (!container) return;
    
    container.innerHTML = `
        <div class="recipe-detail">
            <div class="mb-4">
                <a href="dashboard.php" class="btn-secondary" onclick="clearRecipeSelection()">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
            <div class="recipe-hero">
                <img src="${escapeHtml(recipe.image_url || 'https://via.placeholder.com/600x400?text=Recipe')}" 
                     alt="${escapeHtml(recipe.title)}"
                     class="recipe-hero-image"
                     loading="lazy"
                     decoding="async"
                     width="600"
                     height="400"
                     onload="this.classList.add('loaded')"
                     onerror="this.onerror=null; this.src='https://via.placeholder.com/600x400?text=Recipe+Image'; this.classList.add('loaded');">
                <div class="recipe-info">
                    <h1>${escapeHtml(recipe.title)}</h1>
                    <p class="recipe-cuisine">${recipe.cuisine_emoji || ''} ${escapeHtml(recipe.cuisine)}</p>
                    <p>${escapeHtml(recipe.description || '')}</p>
                    
                    <div class="recipe-meta">
                        <p><strong>Prep Time:</strong> ${recipe.timing.prep_time} minutes</p>
                        <p><strong>Cook Time:</strong> ${recipe.timing.cook_time} minutes</p>
                        <p><strong>Servings:</strong> ${recipe.timing.servings}</p>
                    </div>
                    
                    <div class="recipe-badges">
                        ${recipe.is_veg ? '<span class="badge badge-veg">🌱 Vegetarian</span>' : ''}
                        ${recipe.is_high_protein ? '<span class="badge badge-protein">💪 High Protein</span>' : ''}
                    </div>
                </div>
            </div>
            
            <div class="nutrition-section">
                <h2>Nutrition Information</h2>
                <table class="nutrition-table">
                    <thead>
                        <tr>
                            <th>Nutrient</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Calories</td>
                            <td>${recipe.nutrition.calories} kcal</td>
                        </tr>
                        <tr>
                            <td>Protein</td>
                            <td>${recipe.nutrition.proteins_g} g</td>
                        </tr>
                        <tr>
                            <td>Carbohydrates</td>
                            <td>${recipe.nutrition.carbs_g} g</td>
                        </tr>
                        <tr>
                            <td>Fats</td>
                            <td>${recipe.nutrition.fats_g} g</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="ingredients-section">
                <h2>Ingredients</h2>
                <ul class="ingredients-list">
                    ${recipe.ingredients.map(ing => `
                        <li>
                            <strong>${escapeHtml(ing.qty)} ${escapeHtml(ing.unit)}</strong> 
                            ${escapeHtml(ing.item)}
                        </li>
                    `).join('')}
                </ul>
            </div>
            
            <div class="steps-section">
                <h2>Instructions</h2>
                <ol class="steps-list">
                    ${recipe.steps.map(step => `
                        <li>${escapeHtml(step.instruction)}</li>
                    `).join('')}
                </ol>
            </div>
        </div>
    `;
}

// Authentication Functions
async function checkAuthStatus() {
    try {
        // Use fetch directly to avoid showing error alerts for expected 401 responses
        const response = await fetch(`${API_BASE}/auth.php?action=me`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            updateAuthUI(data.data);
        } else {
            // User not logged in - this is expected on login/register pages
            updateAuthUI(null);
        }
    } catch (error) {
        // User not logged in - silently handle
        updateAuthUI(null);
    }
}

function updateAuthUI(user) {
    const authSection = document.getElementById('auth-section');
    if (!authSection) return;
    
    if (user) {
        authSection.innerHTML = `
            <span>Welcome, ${escapeHtml(user.username)}!</span>
            ${user.is_premium ? '<span class="badge badge-protein">⭐ Premium</span>' : ''}
            <button class="btn-secondary" onclick="logout()">Logout</button>
        `;
    } else {
        authSection.innerHTML = `
            <a href="login.php" class="btn-secondary">Login</a>
            <a href="register.php" class="btn-primary">Sign Up</a>
        `;
    }
}

async function logout() {
    try {
        await apiCall('auth.php?action=logout', { method: 'POST' });
        window.location.reload();
    } catch (error) {
        console.error('Logout failed:', error);
    }
}

// Utility Functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showAlert(type, message) {
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) return;
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    alert.setAttribute('role', 'alert');
    
    alertContainer.innerHTML = '';
    alertContainer.appendChild(alert);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// Clear recipe selection when going back to dashboard
function clearRecipeSelection() {
    sessionStorage.removeItem('selectedRecipeId');
}

// Export for use in other scripts
window.AppState = AppState;
window.loadRecipeDetail = loadRecipeDetail;
window.clearRecipeSelection = clearRecipeSelection;

