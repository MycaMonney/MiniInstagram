// Constantes
const urlApiUsers = "http://localhost/GitHub/MiniInstagram/BackEnd/API/squelleteUser.php";
const urlApiFriendship = "http://localhost/GitHub/MiniInstagram/BackEnd/API/squelleteFriendship.php";

const listUsers = document.getElementById("listUsers");
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

        console.log("📝 Envoi du nouvel utilisateur :", dataObj);
        await postUser(dataObj);
    });
}

// Soumission du formulaire de connexion
document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById("loginForm");

    if (loginForm) {
        console.log("✅ Formulaire de connexion détecté");

        loginForm.addEventListener("submit", function (event) {
            event.preventDefault();

            const username = document.getElementById("loginUsername").value.trim();
            console.log("📤 Tentative de connexion :", username);

            if (username) {
                loginUser(username);
            } else {
                alert("⚠ Veuillez entrer un nom d'utilisateur !");
            }
        });
    }
});

// ➕ Ajouter un ami
function addFriend(idFriend) {
    console.log("🚨 Fonction addFriend appelée avec ID ami :", idFriend);

    const userData = sessionStorage.getItem("user");
    console.log("🔍 Données brutes dans sessionStorage :", userData);


    const currentUser = JSON.parse(sessionStorage.getItem("user"));

    if (!currentUser || !currentUser.idUser) {
        alert("❌ Vous devez être connecté pour ajouter un ami.");
        return;
    }

    const nouvelleAmitie = {
        user_id_1: currentUser.idUser,
        user_id_2: idFriend
    };

    console.log("👥 Création d'une amitié :", nouvelleAmitie);
    postAmitie(nouvelleAmitie);
}

// Envoi d'une nouvelle amitié
async function postAmitie(donnees) {
    try {
        console.log("✅ Fonction postAmitie déclenchée !");
        console.log("📡 Envoi de la requête POST :", urlApiFriendship);

        const response = await fetch(urlApiFriendship, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(donnees),
        });

        const textResponse = await response.text();
        console.log("📩 Réponse brute du serveur :", textResponse);

        let jsonData;
        try {
            jsonData = JSON.parse(textResponse);
        } catch (err) {
            console.error("❌ Erreur de parsing JSON :", err);
            console.warn("🔎 Contenu reçu non-JSON :", textResponse);
            return;
        }

        if (jsonData.success) {
            alert("🎉 Amitié ajoutée avec succès !");
            getUsers();
        }
    } catch (error) {
        console.error("❌ Erreur lors de l'ajout de l'amitié :", error);
    }
}

// ➕ Ajouter un utilisateur + connexion automatique
async function postUser(donnees) {
    try {
        console.log("📡 Envoi de la requête POST :", urlApiUsers);

        const response = await fetch(urlApiUsers, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(donnees),
        });

        const textResponse = await response.text();
        console.log("📩 Réponse brute du serveur :", textResponse);

        const jsonData = JSON.parse(textResponse);

        if (jsonData.success) {
            alert("🎉 Utilisateur ajouté avec succès !");
            if (jsonData.user) {
                sessionStorage.setItem("user", JSON.stringify(jsonData.user));
                console.log("🔐 Utilisateur connecté :", jsonData.user);
            }
            getUsers();
        }
    } catch (error) {
        console.error("❌ Erreur lors de l'ajout :", error);
    }
}

// 🔐 Connexion d'un utilisateur
async function loginUser(username) {
    try {
        const response = await fetch(urlApiUsers, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ action: "login", username: username }),
        });

        const textResponse = await response.text();
        console.log("📩 Réponse brute du serveur :", textResponse);

        const jsonData = JSON.parse(textResponse);

        if (jsonData.success) {
            sessionStorage.setItem("user", JSON.stringify(jsonData.user));
            alert("✅ Connexion réussie !");
            window.location.href = "../index.html";
        } else {
            alert("❌ Utilisateur non trouvé !");
        }
    } catch (error) {
        console.error("🚨 Erreur lors de la connexion :", error);
    }
}

// 🔄 Récupérer les utilisateurs
async function getUsers(adminMode = false) {
    if (!listUsers) return;

    try {
        console.log("📡 Chargement des utilisateurs...");
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
        console.error("❌ Erreur lors du chargement des utilisateurs :", error);
    }
}

// ❌ Supprimer un utilisateur
async function deleteUser(idUser) {
    if (!confirm("⚠ Voulez-vous vraiment supprimer cet utilisateur ?")) return;

    try {
        const response = await fetch(urlApiUsers + "?idUser=" + idUser, {
            method: "DELETE",
            headers: { "Content-Type": "application/json" }
        });

        const textResponse = await response.text();
        const jsonData = JSON.parse(textResponse);

        if (jsonData.message) {
            alert("✅ Utilisateur supprimé !");
            getUsers();
        } else {
            alert("❌ Erreur : " + (jsonData.error || "Impossible de supprimer l'utilisateur."));
        }
    } catch (error) {
        console.error("🚨 Erreur lors de la suppression :", error);
    }
}
async function getFriends() {
    const friendsList = document.getElementById("friendsList");
    friendsList.innerHTML = ""; // Vider au cas où

    const currentUser = JSON.parse(sessionStorage.getItem("user"));

    if (!currentUser || !currentUser.idUser) {
        friendsList.innerHTML = "<p>❌ Vous devez être connecté pour voir vos amis.</p>";
        return;
    }

    try {
        const response = await fetch(`${urlApiFriendship}?id=${currentUser.idUser}`);
        const textResponse = await response.text();
        console.log("🧪 Réponse brute reçue dans getFriends:", textResponse);

        let friendsData;
        try {
            friendsData = JSON.parse(textResponse);
        } catch (err) {
            console.error("❌ Erreur de parsing JSON :", err);
            friendsList.innerHTML = "<p>⚠ Impossible d'afficher les amis.</p>";
            return;
        }

        if (!Array.isArray(friendsData) || friendsData.length === 0) {
            friendsList.innerHTML = "<p>🤷 Aucun ami pour l'instant.</p>";
            return;
        }

        const titre = document.createElement("h3");
        titre.textContent = "👥 Liste de vos amis";
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
        console.error("❌ Erreur lors du chargement des amis :", error);
        friendsList.innerHTML = "<p>❌ Une erreur est survenue.</p>";
    }
}



// 📦 Chargement des utilisateurs au démarrage
document.addEventListener("DOMContentLoaded", function () {
    if (listUsers) {
        getUsers();
    }
    getFriends();
});
