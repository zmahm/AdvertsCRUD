/**
 *
 * Handles the inline confirmation for delete actions in tables
 *
 * Usage:
 * - Ensure that each delete button calls `showConfirm(advertId)` when clicked.
 * - The confirmation box should contain a cancel button that calls `hideConfirm(advertId)`.
 *
 * Dependencies:
 * - The HTML structure must include a `confirm-box` element for each advert,
 *   with an ID format of `confirmBox{advertId}`.
 *
 * Notes:
 * - The confirmation boxes are hidden by default using the CSS `hidden` class.
 * - The script ensures only one confirmation box is visible at a time.
 */

function showConfirm(id) {
    // Hide all other confirm boxes
    document.querySelectorAll('.confirm-box').forEach(box => {
        box.classList.add('hidden');
    });

    // Show the selected confirm box
    const confirmBox = document.getElementById('confirmBox' + id);
    if (confirmBox) {
        confirmBox.classList.remove('hidden');
    }
}

function hideConfirm(id) {
    // Hide the selected confirm box
    const confirmBox = document.getElementById('confirmBox' + id);
    if (confirmBox) {
        confirmBox.classList.add('hidden');
    }
}
