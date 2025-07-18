import React, { useEffect, useState } from "react";
console.log("React app mounted on #react-espace-membre");
export default function App() {
  const [user, setUser] = useState(undefined);

  useEffect(() => {
    fetch(FCJMP_REACT.rest_url + "wp/v2/users/me", {
      headers: {
        "X-WP-Nonce": FCJMP_REACT.nonce,
      },
      credentials: "include",
    })
      .then((res) => {
        if (!res.ok) {
          setUser(null);
          return null;
        }
        return res.json();
      })
      .then((data) => {
        if (data) {
          setUser(data);
        }
      });
  }, []);

  if (user === undefined) {
    return <p>Chargement en cours…</p>;
  }

  if (user === null) {
    return <p>Vous devez être connecté pour accéder à cet espace.</p>;
  }

  return (
    <div>
      <h1>Bonjour, {user.name} !</h1>
      <p>Bienvenue dans votre espace membre bblou.</p>
    </div>
  );
}
