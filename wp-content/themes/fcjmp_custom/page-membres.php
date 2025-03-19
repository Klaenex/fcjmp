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

        async function fetchMembers() {
            const query = `
            query {
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

                const data = await response.json();
                console.log("Data received:", data.data);

                if (data.data && data.data.boards.length > 0) {
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
                "6": document.getElementById("memberListNamur")
            };

            Object.values(regions).forEach(list => list.innerHTML = "");

            members.forEach((member) => {
                const region = member.column_values[6]?.text;
                if (!region || !regions[region]) return;


                const niveauMJ = member.column_values[0]?.text;
                const email = member.column_values[1]?.text;
                const adress = member.column_values[2]?.text;
                const phone = member.column_values[3]?.text;
                const instagram = member.column_values[4]?.text;
                const facebook = member.column_values[5]?.text;
                const logo = member.column_values[6]?.text;


                console.log(member)

                const listItem = document.createElement("li");
                listItem.innerHTML = `
                <div class="card-item">
                    <strong class="card-title">${member.name}</strong>
                   
                    ${phone ? `<p>Téléphone: ${phone}</p>` : ''}
                    ${instagram ? `<a href="${instagram}"><img class="logo logo-member" src="<?php echo get_template_directory_uri(); ?>/assets/img/logo/instagram.png" alt="Instagram" loading="lazy"></a>` : ''}
                    ${facebook ? `<a href="${facebook}"><img class="logo logo-member" src="<?php echo get_template_directory_uri(); ?>/assets/img/logo/facebook.png" alt="Facebook" loading="lazy"></a>` : ''}
                    ${email ? `<a href="mailto:${email}"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/mail.svg" class="logo logo-member" alt="Icône Email" loading="lazy"></a>` : ''}
                </div>`;

                regions[region].appendChild(listItem);
            });
        }

        fetchMembers();
    });
</script>

<section class="section section-green">
    <div class="section-green_wrap">
        <h1 class="title title-big">Nos membres</h1>
    </div>
</section>

<section class="section">
    <div class="content">
        <h2>Bruxelles</h2>
        <ul id="memberListBruxelles" class="list-member"></ul>

        <h2>Hainaut</h2>
        <ul id="memberListHainaut" class="list-member"></ul>

        <h2>Brabant Wallon</h2>
        <ul id="memberListBrabantWallon" class="list-member"></ul>

        <h2>Liège</h2>
        <ul id="memberListLiege" class="list-member"></ul>

        <h2>Luxembourg</h2>
        <ul id="memberListLuxembourg" class="list-member"></ul>

        <h2>Namur</h2>
        <ul id="memberListNamur" class="list-member"></ul>
    </div>
</section>

<?php get_footer(); ?>