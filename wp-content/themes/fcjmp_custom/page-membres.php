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
        const templateDirectoryUri = "<?php echo get_template_directory_uri(); ?>";

        if (!apiKey) {
            console.error("API Key is not defined.");
            return;
        }

        const boardId = "3180263669";
        const limitQuery = 100;

        async function fetchMembers() {
            const query = `
            query {
                complexity {
                    query
                    before
                    after
                }
                boards(ids: [${boardId}]) {
                    items_page (limit:${limitQuery}) {
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
            const regions = {
                "1": document.getElementById("memberListBruxelles"),
                "2": document.getElementById("memberListHainaut"),
                "3": document.getElementById("memberListBrabantWallon"),
                "4": document.getElementById("memberListLiege"),
                "5": document.getElementById("memberListLuxembourg"),
                "6": document.getElementById("memberListNamur"),
            };

            Object.values(regions).forEach((list) => (list.innerHTML = ""));

            members.forEach((member, index) => {
                console.log("Processing member:", member);
                const listItem = document.createElement("div");
                listItem.classList.add('card-member');

                listItem.innerHTML = `
                <span>
                    <img src="${member.column_values[1].text}" alt="${member.name}"/>
                    <h3>${member.name}</h3>
                </span>
                <span>
                    
                    <p>Téléphone: ${member.column_values[4]?.text || "N/A"}</p>
                    ${
                      member.column_values[3]?.text
                        ? `<a href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(member.column_values[3].text)}" target="_blank" rel="noopener noreferrer">
                            <img src="${templateDirectoryUri}/assets/img/maps.svg" alt="Google Maps"/>
                           </a>`
                        : ""
                    }
                    <a href="mailto:${member.column_values[2]?.text || "N/A"}" > <img src="${templateDirectoryUri}/assets/img/mail.svg" alt="Google Maps"/> </a>  

                </span>
            `;

                const region = member.column_values[7]?.text;
                if (region && regions[region]) {
                    regions[region].appendChild(listItem);
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
        <div class="card" id="memberListBruxelles">
            <!-- Les membres de Bruxelles seront affichés ici -->

        </div>

        <h2>Hainaut</h2>
        <div class="card" id="memberListHainaut">
            <!-- Les membres de Hainaut seront affichés ici -->
        </div>

        <h2>Brabant Wallon</h2>
        <div class="card" id="memberListBrabantWallon">
            <!-- Les membres de Brabant Wallon seront affichés ici -->
        </div>

        <h2>Liège</h2>
        <div class="card" id="memberListLiege">
            <!-- Les membres de Liège seront affichés ici -->
        </div>

        <h2>Luxembourg</h2>
        <div class="card" id="memberListLuxembourg">
            <!-- Les membres de Luxembourg seront affichés ici -->
        </div>

        <h2>Namur</h2>
        <div class="card" id="memberListNamur">
            <!-- Les membres de Namur seront affichés ici -->
        </div>
    </div>
</section>

<?php
get_footer();
