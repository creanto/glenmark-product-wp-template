(function () {
    function moveGlenmarkCategory() {
        const basicCategory = document.getElementById('elementor-panel-category-basic');
        const glenmarkCategory = document.getElementById('elementor-panel-category-glenmark');

        if (basicCategory && glenmarkCategory && basicCategory.nextElementSibling !== glenmarkCategory) {
            basicCategory.after(glenmarkCategory);
        }
    }

    const observer = new MutationObserver(moveGlenmarkCategory);
    observer.observe(document.body, { childList: true, subtree: true });
    moveGlenmarkCategory();
}());