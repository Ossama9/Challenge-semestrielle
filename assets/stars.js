document.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('input[name="comment[note]"]')
    const labels = document.querySelectorAll('#comment_note label')

    labels.forEach((label)=>{
        label.addEventListener('click',function (event){
            const idInput = label.getAttribute('for')
            const element = document.querySelector(`input[id="${idInput}"]`);
            element.checked = true
            event.preventDefault()
        })
    })


})