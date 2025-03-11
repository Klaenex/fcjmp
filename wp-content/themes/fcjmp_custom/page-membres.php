<?php
/*
Template Name: Membres
*/

get_header();

// Charger les variables d'environnement
require_once __DIR__ . '/../../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
$apiKey = $_ENV['API_MONDAY'];
?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        console.log("DOM fully loaded and parsed");

        const apiKey = "<?php echo esc_js($apiKey); ?>";
        if (!apiKey) {
            console.error("API Key is not defined.");
            return;
        }

        const boardId = "3180263669";
        const limitQuery = 100;
        const imgId = "file_mknpca6";

        async function fetchMembers() {
            const query = `
                query {
                    complexity {
                        query
                        before
                        after
                    }
                    boards(ids: [${boardId}]) {
                        items_page (limit:${limitQuery}){
                            items {
                                name
                                column_values(
                                    ids: ["texte", "email", "location", "phone", "link_mknht68g", "link_mknhjhpb", "numeric_mknpawtj"]
                                ) {
                                    id
                                    text
                                }
                            }
                        }
                    }
                }
            `;

            try {
                const response = await fetch("https://api.monday.com/v2", {
                    method: "POST",
                    headers: {
                        "Authorization": apiKey,
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        query
                    }),
                });
                console.log(response);

                const data = await response.json();
                console.log("Data received:", data.data);

                if (data.data && data.data.boards && data.data.boards.length > 0) {
                    displayMembers(data.data.boards[0].items_page.items);
                } else {
                    console.error("No data found or invalid data structure.");
                }
            } catch (error) {
                console.error("Error fetching members:", error);
            }
        }

        function displayMembers(members) {
            const memberListBruxelles = document.getElementById("memberListBruxelles");
            const memberListHainaut = document.getElementById("memberListHainaut");
            const memberListBrabantWallon = document.getElementById("memberListBrabantWallon");
            const memberListLiege = document.getElementById("memberListLiege");
            const memberListLuxembourg = document.getElementById("memberListLuxembourg");
            const memberListNamur = document.getElementById("memberListNamur");

            if (!memberListBruxelles || !memberListHainaut || !memberListBrabantWallon || !memberListLiege || !memberListLuxembourg || !memberListNamur) {
                console.error("One or more member lists are not found in the DOM.");
                return;
            }

            memberListBruxelles.innerHTML = "";
            memberListHainaut.innerHTML = "";
            memberListBrabantWallon.innerHTML = "";
            memberListLiege.innerHTML = "";
            memberListLuxembourg.innerHTML = "";
            memberListNamur.innerHTML = "";

            members.forEach((member) => {

                console.log("Processing member:", member);
                const listItem = document.createElement("li");
                listItem.innerHTML = `
                <div class="card-item">
                    <strong class="card-title">${member.name}</strong>
                    
                    <a href="mailto:${member.column_values[1]?.text || "N/A"}">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/mail.svg" class="logo logo-member" alt="Icône Email" loading="lazy">
                    </a>

                    Localisation: ${member.column_values[2]?.text || "N/A"}

                    Téléphone: ${member.column_values[3]?.text || "N/A"}
                    
                    <a href="${member.column_values[4]?.text || "N/A"}">
                        <img class="logo logo-member" src="<?php echo get_template_directory_uri(); ?>/assets/img/logo/instagram.png" alt="" loading="lazy">
                    </a> 

                    <a href="${member.column_values[5]?.text || "N/A"}">
                        <img class="logo logo-member" src="<?php echo get_template_directory_uri(); ?>/assets/img/logo/facebook.png" alt="" loading="lazy">
                    </a> 
                </div>`;

                // Vérifiez si la valeur est un nombre avant de trier
                const region = member.column_values[6]?.text;
                if (region && !isNaN(region)) {
                    switch (region) {
                        case "1":
                            memberListBruxelles.appendChild(listItem);
                            break;
                        case "2":
                            memberListHainaut.appendChild(listItem);
                            break;
                        case "3":
                            memberListBrabantWallon.appendChild(listItem);
                            break;
                        case "4":
                            memberListLiege.appendChild(listItem);
                            break;
                        case "5":
                            memberListLuxembourg.appendChild(listItem);
                            break;
                        case "6":
                            memberListNamur.appendChild(listItem);
                            break;
                    }
                }
            });
        }

        fetchMembers();
    });
</script>

<section class="section">
    <div class="content">
        <h1 class="title title-big">Nos membres</h1>

        <h2>Bruxelles</h2>
        <ul id="memberListBruxelles" class=" member-list">

        </ul>

        <h2>Hainaut</h2>
        <ul id="memberListHainaut">
        </ul>

        <h2>Brabant Wallon</h2>
        <ul id="memberListBrabantWallon">
        </ul>

        <h2>Liège</h2>
        <ul id="memberListLiege">
        </ul>

        <h2>Luxembourg</h2>
        <ul id="memberListLuxembourg">
        </ul>

        <h2>Namur</h2>
        <ul id="memberListNamur">
        </ul>
    </div>
</section>

<?php
get_footer();
?>