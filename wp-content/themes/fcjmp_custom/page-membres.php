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

        const boardId = "3180263669"; // Remplacez par l'ID de votre tableau Monday.com

        async function fetchMembers() {
            const query = `
               query {
                boards(ids: "${boardId}") {
                    items_page {
                    cursor
                        items {
                            id
                            name
                            subitems {
                            id
                            name
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

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (!data.data || !data.data.boards || !data.data.boards[0].items) {
                    throw new Error("Invalid response structure");
                }

                displayMembers(data.data.boards[0].items);
            } catch (error) {
                console.error("Error fetching members:", error);
            }
        }


        // function displayMembers(members) {
        //     const memberList = document.getElementById("memberList");
        //     if (!memberList) return;

        //     memberList.innerHTML = "";
        //     members.forEach((member) => {
        //         const row = document.createElement("tr");
        //         row.innerHTML = `
        //             <td>${member.name}</td>
        //             <td>${member.column_values[2]?.text || ""}</td> <!-- Adaptez l'index selon votre configuration -->
        //             <td>${member.column_values[3]?.text || ""}</td>
        //             <td>${member.column_values[4]?.text || ""}</td>
        //             <td>${member.column_values[5]?.text || ""}</td>
        //         `;
        //         memberList.appendChild(row);
        //     });
        // }

        fetchMembers();
    });
</script>

<div class="wrap">
    <h1>Membres de la FCJMP</h1>
    <table class="member-table">
        <thead>
            <tr>
                <th>Employé</th>
                <th>Email</th>
                <th>Localisation</th>
                <th>Téléphone</th>
                <th>Coordonnateur</th>
            </tr>
        </thead>
        <tbody id="memberList">
            <!-- Les membres seront affichés ici -->
        </tbody>
    </table>
</div>

<?php
get_footer();
?>