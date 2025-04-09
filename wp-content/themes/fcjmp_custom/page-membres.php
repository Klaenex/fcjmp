<?php
/*
 Template Name: Page des Membres
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
                                     ids: ["texte", "email", "location", "phone", "link_mknht68g", "link_mknhjhpb", "numeric_mknpawtj","file_mknpca6"]
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

            let i = 0;
            members.forEach((member) => {
                i++;
                console.log("Processing member:", member);
                const listItem = document.createElement("li");
                listItem.innerHTML = `
                     <strong>${i}. ${member.name}</strong><br>
                     Email: ${member.column_values[1]?.text || "N/A"}<br>
                     Localisation: ${member.column_values[2]?.text || "N/A"}<br>
                     Téléphone: ${member.column_values[3]?.text || "N/A"}<br>
                     Instagram: ${member.column_values[4]?.text || "N/A"}<br>
                     Facebook: ${member.column_values[5]?.text || "N/A"}
                 `;

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
        <h1>Membres de la FCJMP</h1>

        <h2>Bruxelles</h2>
        <ul id="memberListBruxelles">
            <!-- Les membres de Bruxelles seront affichés ici -->

        </ul>

        <h2>Hainaut</h2>
        <ul id="memberListHainaut">
            <!-- Les membres de Hainaut seront affichés ici -->
        </ul>

        <h2>Brabant Wallon</h2>
        <ul id="memberListBrabantWallon">
            <!-- Les membres de Brabant Wallon seront affichés ici -->
        </ul>

        <h2>Liège</h2>
        <ul id="memberListLiege">
            <!-- Les membres de Liège seront affichés ici -->
        </ul>

        <h2>Luxembourg</h2>
        <ul id="memberListLuxembourg">
            <!-- Les membres de Luxembourg seront affichés ici -->
        </ul>

        <h2>Namur</h2>
        <ul id="memberListNamur">
            <!-- Les membres de Namur seront affichés ici -->
        </ul>
    </div>
</section>

<?php
get_footer();
