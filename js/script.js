//Constantes
const urlApi = "http://localhost/GitHub/MiniInstagram/BackEnd/API/squelleteUser.php";
const listUsers = document.getElementById("listUsers");
const formulaire = document.getElementById("formulaire");

//Ajout automatique d'une photo de profil aléatoire
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

//Vérification et soumission du formulaire d'inscription
if (formulaire) {
    formulaire.addEventListener("submit", async (event) => {
        event.preventDefault();
        const formData = new FormData(formulaire);
        const dataObj = Object.fromEntries(formData);

        console.log("Envoi de l'utilisateur :", dataObj);
        await postUser(dataObj);
    });
}

//Vérification et soumission du formulaire de connexion
document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById("loginForm");

    if (loginForm) {
        console.log("✅ Formulaire de connexion détecté !");

        loginForm.addEventListener("submit", function (event) {
            event.preventDefault();

            const username = document.getElementById("loginUsername").value.trim();
            console.log("📤 Tentative de connexion avec :", username);

            if (username) {
                loginUser(username);
            } else {
                alert("⚠ Veuillez entrer un nom d'utilisateur !");
            }
        });
    } else {
        console.warn("❌ Formulaire de connexion non trouvé !");
    }
});

//Ajouter un utilisateur
async function postUser(donnees) {
    try {
        console.log("📡 Envoi de la requête POST :", urlApi);

        const response = await fetch(urlApi, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(donnees),
        });

        const textResponse = await response.text();
        console.log("📩 Réponse brute du serveur :", textResponse);

        const jsonData = JSON.parse(textResponse);
        if (jsonData.success) {
            alert("🎉 Utilisateur ajouté avec succès !");
            getUsers(); // 🔄 Rafraîchir la liste
        } else {
            alert("❌ Erreur lors de l'ajout : " + (jsonData.error || "Problème inconnu."));
        }
    } catch (error) {
        console.error("❌ Erreur lors de l'ajout :", error);
    }
}

//Connexion utilisateur
async function loginUser(username) {
    try {
        console.log("📤 Tentative de connexion avec :", username);

        const response = await fetch(urlApi, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ action: "login", username: username }),
        });

        const textResponse = await response.text();
        console.log("📩 Réponse brute du serveur :", textResponse);

        const jsonData = JSON.parse(textResponse);

        if (jsonData.success) {
            console.log("✅ Connexion réussie :", jsonData.user);
            alert("Connexion réussie !");
            sessionStorage.setItem("user", JSON.stringify(jsonData.user)); // Stocker en session
            window.location.href = "../index.html";
        } else {
            alert("❌ Utilisateur non trouvé !");
        }
    } catch (error) {
        console.error("🚨 Erreur lors de la connexion :", error);
    }
}

//Récupérer et afficher les utilisateurs
async function getUsers(adminMode = false) {
    if (!listUsers) return;

    try {
        console.log("📡 Chargement des utilisateurs...");
        const response = await fetch(urlApi);
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

//Supprimer un utilisateur
async function deleteUser(idUser) {
    if (!confirm("⚠ Voulez-vous vraiment supprimer cet utilisateur ?")) return;

    console.log("📤 Suppression de l'utilisateur avec ID :", idUser);

    try {
        const response = await fetch(urlApi + "?idUser=" + idUser, {
            method: "DELETE",
            headers: { "Content-Type": "application/json" }
        });

        const textResponse = await response.text();
        console.log("📩 Réponse brute du serveur :", textResponse);

        const jsonData = JSON.parse(textResponse);
        if (jsonData.message) {
            alert("✅ Utilisateur supprimé !");
            getUsers(); // 🔄 Rafraîchir la liste
        } else {
            alert("❌ Erreur : " + (jsonData.error || "Impossible de supprimer l'utilisateur."));
        }
    } catch (error) {
        console.error("🚨 Erreur lors de la suppression :", error);
    }
}

// 📌 Charger les utilisateurs au démarrage
document.addEventListener("DOMContentLoaded", function () {
    if (listUsers) {
        getUsers();
    }
});
