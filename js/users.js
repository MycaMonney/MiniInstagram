// Constantes
const urlApiUsers = "https://10.5.57.106/GitHub/MiniInstagram/BackEnd/API/squelleteUser.php";
const urlApiFriendship = "https://10.5.57.106/GitHub/MiniInstagram/BackEnd/API/squelleteFriendship.php";
const urlApiPhoto = "https://10.5.57.106/GitHub/MiniInstagram/BackEnd/API/squelletePhoto.php";

const listUsers = document.getElementById("listUsers");
const listAmis = document.getElementById("friendsList");
const listPosts = document.getElementById("listPosts");
const formulaire = document.getElementById("formulaire");

// Ajout automatique d'une photo de profil aléatoire
document.addEventListener("DOMContentLoaded", function () {
    let nbrPics = Math.floor(Math.random() * 99);
    let sexe = Math.floor(Math.random() * 2);
    let url = sexe === 1
        ? `https://randomuser.me/api/portraits/women/${nbrPics}.jpg`
        : `https://randomuser.me/api/portraits/men/${nbrPics}.jpg`;

    let urlInput = document.getElementById("urlPdP");
    if (urlInput) {
        urlInput.value = url;
    }
});

// Soumission du formulaire d'inscription
if (formulaire) {
    formulaire.addEventListener("submit", async (event) => {
        event.preventDefault();
        const formData = new FormData(formulaire);
        const dataObj = Object.fromEntries(formData);

        console.log("DATA :", dataObj);
        await postUser(dataObj);
    });
}

// Soumission du formulaire de connexion
document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById("loginForm");

    if (loginForm) {
        console.log("Formulaire de connexion détecté");

        loginForm.addEventListener("submit", function (event) {
            event.preventDefault();

            const username = document.getElementById("loginUsername").value.trim();
            const mdp = document.getElementById("loginMdp").value.trim();

            console.log("Tentative de connexion :", username);

            if (username && mdp) {
                loginUser(username, mdp);
            } else {
                alert("Veuillez entrer un nom d'utilisateur et un MDP !");
            }
        });
    }
});

//Ajouter un ami
function addFriend(idFriend) {
    console.log("Fonction addFriend appelée avec ID ami :", idFriend);

    const token = sessionStorage.getItem("token");

    if (!token) {
        alert("Vous devez être connecté pour ajouter un ami.");
        return;
    }

    const nouvelleAmitie = {
        actual_user_token: token,
        user_id_2: idFriend
    };

    console.log("Amitié :", nouvelleAmitie);
    postAmitie(nouvelleAmitie);
}

// Envoi d'une nouvelle amitié
async function postAmitie(donnees) {
    try {
        const response = await fetch(urlApiFriendship, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(donnees),
        });

        const textResponse = await response.text();
        let jsonData;

        try {
            jsonData = JSON.parse(textResponse);
        } catch (err) {
            console.error("Erreur de parsing JSON :", err);
            console.warn("Contenu reçu avant le parse :", textResponse);
            return;
        }

        if (jsonData.success) {
            alert("Amitié ajoutée avec succès !");
            getUsers();
        }
    } catch (error) {
        console.error("Erreur lors de l'ajout de l'amitié :", error);
    }
}

//A VOIR
// Récupérer les données d'un utilisateur avec son id
async function getUserById(idUser) {
    try {
        // On envoie l'ID de l'utilisateur dans l'URL de la requête GET
        const response = await fetch(`${urlApiUsers}?id=${idUser}`, {
            method: "GET", // Méthode GET pour récupérer des données
            headers: {
                "Content-Type": "application/json"
            }
        });

        const textResponse = await response.text();
        let jsonData;

        try {
            jsonData = JSON.parse(textResponse); // Parsing de la réponse JSON
            return jsonData;
        } catch (err) {
            console.error("Erreur de parsing JSON :", err);
            console.warn("Contenu reçu avant le parse :", textResponse);
            return;
        }
    } catch (error) {
        console.error("Erreur lors de la récupération de l'utilisateur :", error);
    }
}


//Ajouter un utilisateur + connexion automatique
async function postUser(donnees) {
    try {        
        const response = await fetch(urlApiUsers, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(donnees),
        });

        const textResponse = await response.text();
        console.log(textResponse);
        const jsonData = JSON.parse(textResponse);

        if (jsonData.success) {
            alert("Utilisateur ajouté avec succès !");
            if (jsonData.user) {
                sessionStorage.setItem("user", JSON.stringify(jsonData.user));
                console.log("Utilisateur connecté :", jsonData.user);
            }
            getUsers();
        }
    } catch (error) {
        console.error("Erreur lors de l'ajout :", error);
    }
}

//Connexion d'un utilisateur
async function loginUser(username, mdp) {
    try {
        const response = await fetch(urlApiUsers, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ action: "login", username: username, mdp: mdp }),
        });

        const textResponse = await response.text();
            const jsonData = JSON.parse(textResponse);

        if (jsonData.success) {
            sessionStorage.setItem("user", JSON.stringify(jsonData.user));
            sessionStorage.setItem("token", jsonData.token);
            alert("Connexion réussie !");
            window.location.href = "../index.html";
        } else {
            alert("Utilisateur non trouvé !");
        }
    } catch (error) {
        console.error("Erreur lors de la connexion :", error);
    }
}

// Récupérer les utilisateurs
async function getUsers(adminMode = false) {
    if (!listUsers) return;

    try {
        console.log("Chargement des utilisateurs...");
        const response = await fetch(urlApiUsers);
        if (!response.ok) throw new Error("Erreur lors de la récupération des utilisateurs");

        const data = await response.json();
        listUsers.innerHTML = '';

        if (data.length === 0) {
            listUsers.innerHTML = "<p>Aucun utilisateur trouvé.</p>";
            return;
        }

        let tableauUsers = document.createElement("table");
        tableauUsers.className = "table table-striped";

        let ligneTitre = document.createElement("tr");
        let titres = ["Id", "Username", "Photo de profil", "Actions"];

        titres.forEach(textTitres => {
            let titre = document.createElement("th");
            titre.textContent = textTitres;
            ligneTitre.appendChild(titre);
        });

        tableauUsers.appendChild(ligneTitre);

        data.forEach(user => {
            let ligne = document.createElement("tr");

            ["idUser", "username"].forEach(key => {
                let cell = document.createElement("td");
                cell.textContent = user[key] ?? "N/A";
                ligne.appendChild(cell);
            });

            let imgCell = document.createElement("td");
            let img = document.createElement("img");
            img.src = user.urlPdP;
            img.alt = "Photo de profil";
            img.width = 50;
            imgCell.appendChild(img);
            ligne.appendChild(imgCell);

            let actionCell = document.createElement("td");

            if (adminMode) {
                let btnSupprimer = document.createElement("button");
                btnSupprimer.textContent = "Supprimer";
                btnSupprimer.className = "btn btn-danger";
                btnSupprimer.addEventListener("click", () => deleteUser(user.idUser));
                actionCell.appendChild(btnSupprimer);
            } else {
                let btnAmi = document.createElement("button");
                btnAmi.textContent = "Ajouter en ami";
                btnAmi.className = "btn btn-info";
                btnAmi.addEventListener("click", () => addFriend(user.idUser));
                actionCell.appendChild(btnAmi);
            }

            ligne.appendChild(actionCell);
            tableauUsers.appendChild(ligne);
        });

        listUsers.appendChild(tableauUsers);
    } catch (error) {
        console.error("Erreur lors du chargement des utilisateurs :", error);
    }
}

async function getPosts() {
    const listPosts = document.getElementById("listPosts");
    if (!listPosts) {
        console.error("Erreur : L'élément 'listPosts' n'existe pas dans le DOM.");
        return;
    }

    try {
        console.log("Chargement des posts...");
        const response = await fetch(urlApiPhoto);
        if (!response.ok) throw new Error("Erreur lors de la récupération des posts");

        const textResponse = await response.text();
        console.log(textResponse);
        const data = JSON.parse(textResponse);

        listPosts.innerHTML = '';

        if (data.length === 0) {
            listPosts.innerHTML = "<p>Aucun post trouvé.</p>";
            return;
        }

        for (const post of data) {
            let postContainer = document.createElement("div");
            postContainer.className = "post-container";

            const user = await getUserById(post.user_id);
            
            let pdp = document.createElement("img");
            pdp.src = user.urlPdP;
            pdp.alt = "Photo de profil";
            pdp.className = "pdp-image";
            
            let userInfo = document.createElement("div");
            userInfo.className = "user-info";
            userInfo.textContent = (user.username ?? "Inconnu");


            let img = document.createElement("img");
            img.src = post.photo_url;
            img.alt = "Photo de post";
            img.className = "post-image";

            let createdAt = document.createElement("div");
            createdAt.className = "post-date";
            createdAt.textContent = "Posté le: " + (post.created_at ?? "Date inconnue");

            postContainer.appendChild(pdp);
            postContainer.appendChild(userInfo);
            postContainer.appendChild(img);
            postContainer.appendChild(createdAt);

            listPosts.appendChild(postContainer);
        }
    } catch (error) {
        console.error("Erreur lors du chargement des posts :", error);
        listPosts.innerHTML = "<p>Une erreur est survenue lors du chargement des posts.</p>";
    }
}

//Supprimer un utilisateur
async function deleteUser(idUser) {
    if (!confirm("Voulez-vous vraiment supprimer cet utilisateur ?")) return;

    try {
        const response = await fetch(urlApiUsers + "?idUser=" + idUser, {
            method: "DELETE",
            headers: { "Content-Type": "application/json" }
        });

        const textResponse = await response.text();
        const jsonData = JSON.parse(textResponse);

        if (jsonData.message) {
            alert("Utilisateur supprimé !");
            getUsers();
        } else {
            alert("Erreur : " + (jsonData.error || "Impossible de supprimer l'utilisateur."));
        }
    } catch (error) {
        console.error("Erreur lors de la suppression :", error);
    }
}

async function getFriends() {
    const friendsList = document.getElementById("friendsList");
    if (!friendsList) {
        console.error("Erreur : L'élément 'friendsList' n'existe pas dans le DOM.");
        return;
    }

    friendsList.innerHTML = "";

    const currentUser = sessionStorage.getItem("user");

    if (!currentUser || !currentUser.idUser) {
        friendsList.innerHTML = "<p>Vous devez être connecté pour voir vos amis.</p>";
        return;
    }

    try {
        const response = await fetch(`${urlApiFriendship}?id=${currentUser.idUser}`);
        const textResponse = await response.text();
        let friendsData;
        try {
            friendsData = JSON.parse(textResponse);
        } catch (err) {
            console.error("Erreur de parsing JSON :", err);
            friendsList.innerHTML = "<p>Impossible d'afficher les amis.</p>";
            return;
        }

        if (!Array.isArray(friendsData) || friendsData.length === 0) {
            friendsList.innerHTML = "<p>Aucun ami pour l'instant.</p>";
            return;
        }

        const titre = document.createElement("h3");
        titre.textContent = "Liste de vos amis";
        friendsList.appendChild(titre);

        const ul = document.createElement("ul");
        ul.className = "list-group";

        for (const friend of friendsData) {
            const li = document.createElement("li");
            li.className = "list-group-item d-flex align-items-center gap-3";

            const img = document.createElement("img");
            img.src = friend.urlPdP;
            img.alt = "Photo de profil";
            img.width = 40;
            img.className = "rounded-circle";

            const span = document.createElement("span");
            span.textContent = friend.username;

            li.appendChild(img);
            li.appendChild(span);
            ul.appendChild(li);
        }


        friendsList.appendChild(ul);
    } catch (error) {
        console.error("Erreur lors du chargement des amis :", error);
        friendsList.innerHTML = "<p>Une erreur est survenue.</p>";
    }
}

//Création d'un post
document.addEventListener("DOMContentLoaded", function () {
    const formCreationPost = document.getElementById('creationPost');

    if (formCreationPost) {
        formCreationPost.addEventListener('submit', function (event) {
            event.preventDefault();
            const url = document.getElementById('photo_url').value;

            if (!url || !isValidURL(url)) {
                alert("Veuillez entrer une URL valide !");
                return;
            }

            const currentUserToken = sessionStorage.getItem("token");
            if (!currentUserToken) {
                alert("Vous devez être connecté pour créer un post.");
                return;
            }

            const nouveauPost = {
                user_token: currentUserToken,
                photo_url: url
            };

            console.log("Post :", nouveauPost);
            postPost(nouveauPost);
        });
    } else {
        console.log("Le formulaire de création de post n'est pas présent sur cette page.");
    }
});


function isValidURL(url) {
    try {
        new URL(url);
        return true;
    } catch (e) {
        return false;
    }
}

//Envoi d'un nouveau Post
async function postPost(donnees) {
    try {
        const response = await fetch(urlApiPhoto, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(donnees),
        });

        const textResponse = await response.text();
        let jsonData;

        try {
            jsonData = JSON.parse(textResponse);
        } catch (err) {
            console.error("Erreur de parsing JSON :", err);
            console.warn("Contenu reçu avant le parse :", textResponse);
            return;
        }

        if (jsonData.success) {
            alert("Post ajouté avec succès !");
            getUsers();
        } else {
            console.error("Erreur côté serveur :", jsonData.message || 'Erreur inconnue');
        }
    } catch (error) {
        console.error("Erreur lors de l'ajout du post :", error);
    }
}

//Chargement au démarrage
document.addEventListener("DOMContentLoaded", function () {
    if (listUsers) {
        getUsers();
    }
    if (listPosts) {
        getPosts();
    }
    if (listAmis) {
        getFriends();
    }
});
