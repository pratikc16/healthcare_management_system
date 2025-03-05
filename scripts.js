const switchers = [...document.querySelectorAll('.switcher')]

switchers.forEach(item => {
    item.addEventListener('click', function() {
        switchers.forEach(btn => btn.parentElement.classList.remove('is-active'))
        this.parentElement.classList.add('is-active')
    })
})
