/**
 * This script manages the display of confirmation modals for delete actions.
 * It ensures users explicitly confirm before proceeding with deletions.
 * Usage:
 * - Add `onclick="showConfirm(advertId)"` to delete buttons.
 * - Ensure modals have IDs like `confirmModal<advertId>` and include a "Cancel" button with `onclick="hideConfirm(advertId)"`.
 *
 * Dependencies:
 * - Requires a `hidden` CSS class to toggle modal visibility (`display: none;`).
 * - Assumes unique IDs for each modal corresponding to the item (e.g., advert ID).
 */

function showConfirm(advertId) {
    const modal = document.getElementById(`confirmModal${advertId}`);
    modal.classList.remove('hidden');
    document.body.classList.add('modal-open'); // Prevent background scrolling
}

function hideConfirm(advertId) {
    const modal = document.getElementById(`confirmModal${advertId}`);
    modal.classList.add('hidden');
    document.body.classList.remove('modal-open'); // Re-enable scrolling
}

