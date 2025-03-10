let nbrPics = Math.floor(Math.random(0, 1) * 99);
let sexe = Math.floor(Math.random(0, 1) * 2);
let url = ``;

if (sexe === 1) {
    url = `https://randomuser.me/api/portraits/women/${nbrPics}.jpg`;
} else {
    url = `https://randomuser.me/api/portraits/men/${nbrPics}.jpg`;
}

return url;