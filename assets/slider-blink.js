let count = 1;
function loopSlider() {
    let images = document.querySelectorAll(".imageBack");
    for (let i = 1; i <= images.length; i++) {
        let slide = document.getElementById("slider-" + i);
        if (i === count) {
            slide.style.display = "block";
        } else {
            slide.style.display = "none";
        }
    }

    setInterval(() => {
        if (count === images.length) {
            count = 0;
        }
        count++;
        let currentSlide = document.getElementById("slider-" + count);
        if(currentSlide !== null){
            currentSlide.classList.remove("fadeout");
            currentSlide.style.display = "block";
            currentSlide.classList.add("fadein");
        }

        // Hide the previous slide
        let previousSlide;
        if (count === 1) {
            previousSlide = document.getElementById("slider-" + images.length);
        } else {
            previousSlide = document.getElementById("slider-" + (count - 1));
        }
        if(previousSlide !== null){
            previousSlide.style.display = "none";
            previousSlide.classList.remove("fadein");
            previousSlide.classList.add("fadeout");
        }
    }, 6000);
}

window.addEventListener("load", function() {
    loopSlider();
});
