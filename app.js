/*
 * app.js
 * Two small browser features:
 * 1. Add more ingredient boxes on the recipe form.
 * 2. Save / remove favorites without reloading the page.
 */

// [ADD INGREDIENT FIELD]
// Recipes have different numbers of ingredients, so members add boxes as needed.
// Each new box is also named "ingredients[]" so PHP gets them all in one array.
const addButton = document.getElementById('add-ingredient');

if (addButton) {
  addButton.addEventListener('click', function () {
    const input = document.createElement('input');
    input.name = 'ingredients[]';
    input.maxLength = 100;
    input.placeholder = 'Another ingredient';

    document.getElementById('ingredient-list').appendChild(input);
    input.focus();
  });
}

// [TOGGLE FAVORITE WITHOUT RELOAD]
// fetch() sends a POST to toggle_favorite.php in the background,
// then we update only the button (or list item) using the JSON answer.
const favButtons = document.querySelectorAll('.fav-btn');

favButtons.forEach(function (button) {
  button.addEventListener('click', async function () {
    const data = new FormData();
    data.append('recipe_id', button.dataset.id);

    try {
      const response = await fetch('toggle_favorite.php', { method: 'POST', body: data });
      const result = await response.json();

      if (!result.ok) {
        alert(result.message);
        return;
      }

      // [REMOVE FROM FAVORITES PAGE]
      // On favorites.php the whole recipe disappears from the list.
      const item = button.closest('.fav-item');
      if (item && !result.saved) {
        item.remove();
        return;
      }

      // [UPDATE BUTTON ON RECIPE PAGE]
      if (result.saved) {
        button.textContent = '♥ Saved';
        button.classList.add('saved');
      } else {
        button.textContent = '♡ Save to favorites';
        button.classList.remove('saved');
      }
    } catch (error) {
      alert('Something went wrong. Please try again.');
    }
  });
});
