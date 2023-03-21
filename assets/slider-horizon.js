let count = 1;

function loopSliderHorizontal() {
    let images = document.querySelectorAll(".imageBack");
    for (let i = 1; i <= images.length; i++) {
        let slide = document.getElementById("sliderhorizon-" + i);
        if (i === count) {
            slide.style.display = "block";
        } else {
            slide.style.display = "none";
        }
    }

    setInterval(() => {
        //Create an horizontal slider with fade in and fade out and rounded div indicating index

        if (count === images.length) {
            count = 0;
        }
        count++;
        let currentSlide = document.getElementById("sliderhorizon-" + count);
    });
}


window.addEventListener("load", () => {
   loopSliderHorizontal();
});
