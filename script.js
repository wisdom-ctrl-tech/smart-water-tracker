/* ============================================================
   Smart Water Intake Tracker - script.js
   Small interface helpers. No libraries are used.
   ============================================================ */

// 1. Ask before deleting a water entry.
var deleteForms = document.querySelectorAll('.delete-form');
for (var i = 0; i < deleteForms.length; i++) {
    deleteForms[i].addEventListener('submit', function (event) {
        if (!window.confirm('Delete this water entry?')) {
            event.preventDefault();
        }
    });
}

// 2. Fade the success or error message away after a few seconds.
var flash = document.getElementById('flash-message');
if (flash) {
    window.setTimeout(function () {
        flash.classList.add('message-hide');
        window.setTimeout(function () {
            flash.style.display = 'none';
        }, 700);
    }, 5000);
}

// 3. Show a little feedback on the quick amount button that was clicked.
var quickButtons = document.querySelectorAll('.quick-buttons .btn');
for (var j = 0; j < quickButtons.length; j++) {
    quickButtons[j].addEventListener('click', function () {
        // The text changes only for a moment: the form is submitted straight after.
        this.textContent = 'Adding...';
    });
}
