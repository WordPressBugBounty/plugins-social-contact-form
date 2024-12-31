(() => {
  const target = document.querySelector('.formychat-dropdown')

  if ( !target ) return;

  const placeholder = target.querySelector('.formychat-dropdown-placeholder')
  const search = target.querySelector('.formychat-dropdown-content-search')
  const input = target.querySelector('.formychat-dropdown-input')

  const registerEvents = () => {
    // On Click 
    document.addEventListener('click', (e) => {
      if ( !e.target.closest('.formychat-dropdown') ) {
        return close()
      }

      // on clicked placeholder.
      if (e.target.closest('.formychat-dropdown-placeholder')) {
        return toggle(e)
      }

      // on clicked item
      if (e.target.closest('.formychat-dropdown-content-item') || e.target.classList.contains('formychat-dropdown-content-item')) {
        return choose(e)
      }
    })
    // Keypress.
    document.addEventListener('keyup', (e) => {
      // Ba if target not match the dropdown, return.
      if (! e.target.closest('.formychat-dropdown')) return

      // On search input
      if (e.target.closest('.formychat-dropdown-content-search')) {
        console.log('search', e.target.value.toLowerCase().trim());
        update(e.target.value.toLowerCase().trim())
        return
      }

      // ESC key
      if (e.key === 'Escape') {
        close()
        return
      }
    })

    // Choose default item.
    const defaultItem = target.querySelector('.formychat-dropdown-content-item.selected')
    if (defaultItem) {
      choose({ target: defaultItem })
    }

  }
  window.addEventListener('load', registerEvents)

  // toggle the dropdown.
  toggle = (e) => {
    e.preventDefault()
    if (target.classList.contains('open')) {
      return close()
    }
    open()
  }

  // Open the dropdown.
  const open = () => {
    target.classList.add('active')

    // Focus on search input.
    search.focus()

    // Scroll to selected item.
    const selected = target.querySelector('.formychat-dropdown-content-item.selected')
    if (selected) {
      selected.scrollIntoView({ behavior: 'smooth', block: 'center' })
    } 
  }

  // Close the dropdown.
  const close = () => {
    target.classList.remove('active')
  }

  // Choose the item.
  const choose = (e) => {
    // Unselect all items
    const items = target.querySelectorAll('.formychat-dropdown-content-item')
    items.forEach(i => {
      i.classList.remove('selected')
    })

    // Select clicked item
    e.target.classList.add('selected')

    // Update placeholder
    if (placeholder) {
      placeholder.querySelector('span').textContent = e.target.dataset.placeholder
    }

    // Update hidden input value
    input.value = e.target.dataset.value

    // Reset search.
    search.value = ''
    update()
    close()
  }

  // Update the items.
  const update = (s = '') => {
    const items = target.querySelectorAll('.formychat-dropdown-content-item')
    items.forEach(i => {

      let display = !s|| s.length === 0

      if (s && s.length) {
        display = s.length === 0 || i.dataset.tags.toLowerCase().includes(s)
      }

      i.style.display = display ? 'block' : 'none'
    })
  }
})()
