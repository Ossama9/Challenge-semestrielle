import "./styles/main.scss";
import "./slider-blink.js";
import "./slider-horizon.js";
import "./stars.js";
import '@lottiefiles/lottie-player';


window.axeptioSettings = {
    clientId: "63d78d3d5c656eb1b82491ea",
};

(function(d, s) {
    let t = d.getElementsByTagName(s)[0], e = d.createElement(s);
    e.async = true; e.src = "//static.axept.io/sdk.js";
    t.parentNode.insertBefore(e, t);
})(document, "script");

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();

        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

const mySelectField = document.querySelector("#moreChoices");
if(mySelectField != null) {
    mySelectField.addEventListener("change", function() {
        const selectedOption = this.value;
        if(selectedOption){
            window.location.href = selectedOption;
        }
    });
}

const searchInput = document.getElementById('propertiesSearch');
let resultsContainer = document.getElementById('hotelContainer');
if(searchInput != null) {
    searchInput.addEventListener('input', function () {
        let inputValue = searchInput.value;
        const httpClient = new XMLHttpRequest();
        httpClient.open('POST', '/hotel/search');
        httpClient.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        httpClient.send('search=' + encodeURIComponent(inputValue));

        httpClient.onreadystatechange = function () {
            if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                let response = JSON.parse(this.responseText);
                if(response.length > 0) {
                    resultsContainer.innerHTML = response.map(function(result) {
                        return `
    <article class='rounded-xl bg-white p-3 shadow-lg hover:shadow-xl hover:transform hover:scale-105 duration-300 '>
        <a href='#'>
            <div class='relative flex items-end overflow-hidden rounded-xl'>
                <img src='../../../uploads/image_hotel/${result.image}' class='w-fit h-full' alt='Hotel Photo' loading='lazy' srcset='${result.image}'>
            </div>
            <div class='mt-1 p-2'>
                <h2 class='text-slate-700'>${result.name}</h2>
                <p class='mt-1 text-sm text-slate-400'>${result.city}, ${result.country}</p>

                <div class='mt-3 flex items-end justify-between'>
                    <p class="text-lg font-bold text-blue-500">
                        ${(() => {
                            let stars = "";
                            for (let i = 1; i <= result.note; i++) {
                                stars += "<i style='color: gold;' class='fa fa-star gold'></i>";
                            }
                            return stars;
                        })()}</p>
                    <div class="flex items-center space-x-1.5 rounded-lg bg-blue-500 px-4 py-1.5 text-white duration-100 hover:bg-blue-600">
                        <button class="text-sm"><a href="/hotel/${result.getId}">Voir</a></button>
                    </div>
                </div>
            </div>
        </a>
    </article>
`;
                    }).join('');
                }else{
                    resultsContainer.innerHTML = `<p class='text-slate-400 flex justify-center w-[56vw]'>No results found</p>`;
                }
            }
        }
    });
}

const searchInputAnnounce = document.getElementById('announcesSearch');
let resultsContainerAnnounce = document.getElementById('announceContainer');
if(searchInputAnnounce != null) {
    searchInputAnnounce.addEventListener('input', function () {
        let inputValue = searchInputAnnounce.value;
        const httpClient = new XMLHttpRequest();
        httpClient.open('POST', '/announce/search');
        httpClient.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        httpClient.send('search=' + encodeURIComponent(inputValue));

        httpClient.onreadystatechange = function () {
            if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                let response = JSON.parse(this.responseText);
                if(response.length > 0) {
                    resultsContainerAnnounce.innerHTML = response.map(function(result) {
                        return `
                    <article class="rounded-xl bg-white p-3 shadow-lg hover:shadow-xl hover:transform hover:scale-105 duration-300 ">
                    <a href="#">
                        <div class="relative flex items-end overflow-hidden rounded-xl">
                            <img src="../../uploads/image_hotel/${result.image}" class="w-fit h-full" alt="Announce Photo" loading="lazy" srcset="test">
                        </div>
                        <div class="mt-1 p-2">
                            <h2 class="text-slate-700">${result.name}</h2>
                            <p class="mt-1 text-sm text-slate-400">${result.nbBeds} Lits</p>

                            <div class="mt-3 flex items-end justify-between">
                                <p class="text-lg font-bold text-blue-500">${result.price}€ / Day</p>
                                <div class="flex items-center space-x-1.5 rounded-lg bg-blue-500 px-4 py-1.5 text-white duration-100 hover:bg-blue-600">
                                    <button class="text-sm"><a href="/announce/${result.getId}">Check</a></button>
                                </div>
                            </div>
                        </div>
                    </a>
                </article>
                `;
                    }).join('');
                }else{
                    resultsContainerAnnounce.innerHTML = `<p class='text-slate-400 flex justify-center w-[56vw]'>No results found</p>`;
                }
            }
        }
    });
}

const eye = document.querySelector('.eye');
const password = document.querySelector('#inputPassword');
const passwordConfirm = document.querySelector('#inputPasswordConfirm');

eye.addEventListener('click', () => {
    const condition = password.type === 'password';
    eye.innerHTML = condition ? 'visibility_off' : 'visibility';
    password.type = condition ? 'text' : 'password';
    if (passwordConfirm) passwordConfirm.type = condition ? 'text' : 'password';
});
