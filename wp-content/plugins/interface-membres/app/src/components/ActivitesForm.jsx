import React from "react";
import { cfg } from "../config";
import { createItem } from "../api/content";

export default function ActivitesForm({ onCreated }) {
  // États UI
  const [busy, setBusy] = React.useState(false);
  const [msg, setMsg] = React.useState("");
  const [err, setErr] = React.useState("");

  // Champs requis
  const [title, setTitle] = React.useState("");
  const [description, setDescription] = React.useState("");
  const [date, setDate] = React.useState("");

  // Horaires (optionnels)
  const [startTime, setStartTime] = React.useState("");
  const [endTime, setEndTime] = React.useState("");

  // Lieu
  const [lieuNom, setLieuNom] = React.useState("");
  const [adresse, setAdresse] = React.useState("");
  const [ville, setVille] = React.useState("");
  const [cp, setCp] = React.useState("");
  const [region, setRegion] = React.useState("");

  // Organisation / Inscription / Contact
  const [organisateur, setOrganisateur] = React.useState("");
  const [siteOrganisateur, setSiteOrganisateur] = React.useState("");
  const [inscriptionUrl, setInscriptionUrl] = React.useState("");
  const [contactEmail, setContactEmail] = React.useState("");
  const [contactTel, setContactTel] = React.useState("");

  // Logistique
  const [prix, setPrix] = React.useState("");
  const [capacite, setCapacite] = React.useState("");

  // Divers
  const [infos, setInfos] = React.useState("");

  function Section({ title, children }) {
    return (
      <div className="im-card" style={{ display: "grid", gap: 12 }}>
        <h3>{title}</h3>
        {children}
      </div>
    );
  }

  function validate() {
    if (!title.trim()) return "Le titre est obligatoire.";
    if (!description.trim()) return "La description est obligatoire.";
    if (!date) return "La date est obligatoire.";
    if (!lieuNom.trim()) return "Le nom du lieu est obligatoire.";
    return "";
  }

  async function submit(e) {
    e.preventDefault();
    setMsg("");
    setErr("");

    const vErr = validate();
    if (vErr) {
      setErr(vErr);
      return;
    }

    // Métadonnées envoyées via REST (à déclarer côté plugin pour show_in_rest)
    const meta = {
      im_act_date: date || "",
      im_act_start_time: startTime || "",
      im_act_end_time: endTime || "",
      im_act_lieu_nom: lieuNom || "",
      im_act_adresse: adresse || "",
      im_act_ville: ville || "",
      im_act_cp: cp || "",
      im_act_region: region || "",
      im_act_organisateur: organisateur || "",
      im_act_site_organisateur: siteOrganisateur || "",
      im_act_inscription_url: inscriptionUrl || "",
      im_act_contact_email: contactEmail || "",
      im_act_contact_tel: contactTel || "",
      im_act_prix: prix || "",
      im_act_capacite: capacite || "",
      im_act_infos: infos || "",
    };

    const payload = {
      title,
      content: description,
      meta,
      status: cfg.status.pending, // toujours en relecture par un admin/éditeur
    };

    setBusy(true);
    try {
      await createItem("activites", payload);
      setMsg("Activité soumise pour validation ✅");
      setErr("");

      // Reset des champs
      setTitle("");
      setDescription("");
      setDate("");
      setStartTime("");
      setEndTime("");
      setLieuNom("");
      setAdresse("");
      setVille("");
      setCp("");
      setRegion("");
      setOrganisateur("");
      setSiteOrganisateur("");
      setInscriptionUrl("");
      setContactEmail("");
      setContactTel("");
      setPrix("");
      setCapacite("");
      setInfos("");

      if (typeof onCreated === "function") onCreated();
    } catch (e2) {
      setErr(String(e2));
    } finally {
      setBusy(false);
    }
  }

  return (
    <form
      onSubmit={submit}
      className="im-card"
      style={{ display: "grid", gap: 16 }}
    >
      <h2>Ajouter une activité</h2>

      <Section title="Informations générales">
        <label>
          Titre de l’activité *
          <input
            className="im-input"
            placeholder="ex : Atelier découverte…"
            value={title}
            onChange={(e) => setTitle(e.target.value)}
          />
        </label>

        <label>
          Description *
          <textarea
            className="im-textarea"
            rows={8}
            placeholder="Décrivez l’activité, son déroulé, son public cible, etc."
            value={description}
            onChange={(e) => setDescription(e.target.value)}
          />
        </label>
      </Section>

      <Section title="Date & horaires">
        <div className="im-row" style={{ gap: 12, alignItems: "center" }}>
          <label style={{ minWidth: 140 }}>Date *</label>
          <input
            type="date"
            className="im-input"
            value={date}
            onChange={(e) => setDate(e.target.value)}
          />
        </div>

        <div className="im-row" style={{ gap: 12, alignItems: "center" }}>
          <label style={{ minWidth: 140 }}>Heure de début</label>
          <input
            type="time"
            className="im-input"
            value={startTime}
            onChange={(e) => setStartTime(e.target.value)}
          />
          <label style={{ minWidth: 140 }}>Heure de fin</label>
          <input
            type="time"
            className="im-input"
            value={endTime}
            onChange={(e) => setEndTime(e.target.value)}
          />
        </div>
      </Section>

      <Section title="Lieu">
        <label>
          Nom du lieu *
          <input
            className="im-input"
            placeholder="ex : Maison des Associations"
            value={lieuNom}
            onChange={(e) => setLieuNom(e.target.value)}
          />
        </label>

        <label>
          Adresse
          <input
            className="im-input"
            placeholder="Rue, numéro"
            value={adresse}
            onChange={(e) => setAdresse(e.target.value)}
          />
        </label>

        <div className="im-row" style={{ gap: 12 }}>
          <input
            className="im-input"
            style={{ flex: 2, minWidth: 160 }}
            placeholder="Ville"
            value={ville}
            onChange={(e) => setVille(e.target.value)}
          />
          <input
            className="im-input"
            style={{ flex: 1, minWidth: 120 }}
            placeholder="Code postal"
            value={cp}
            onChange={(e) => setCp(e.target.value)}
          />
          <input
            className="im-input"
            style={{ flex: 2, minWidth: 160 }}
            placeholder="Région (ex : Bruxelles, Wallonie, …)"
            value={region}
            onChange={(e) => setRegion(e.target.value)}
          />
        </div>
      </Section>

      <Section title="Organisation">
        <label>
          Organisateur
          <input
            className="im-input"
            placeholder="Nom de l’organisateur"
            value={organisateur}
            onChange={(e) => setOrganisateur(e.target.value)}
          />
        </label>

        <label>
          Site de l’organisateur (URL)
          <input
            className="im-input"
            type="url"
            placeholder="https://…"
            value={siteOrganisateur}
            onChange={(e) => setSiteOrganisateur(e.target.value)}
          />
        </label>
      </Section>

      <Section title="Inscription & contact">
        <label>
          Lien d’inscription (URL)
          <input
            className="im-input"
            type="url"
            placeholder="https://…"
            value={inscriptionUrl}
            onChange={(e) => setInscriptionUrl(e.target.value)}
          />
        </label>

        <div className="im-row" style={{ gap: 12 }}>
          <input
            className="im-input"
            type="email"
            placeholder="Email de contact"
            value={contactEmail}
            onChange={(e) => setContactEmail(e.target.value)}
          />
          <input
            className="im-input"
            type="tel"
            placeholder="Téléphone"
            value={contactTel}
            onChange={(e) => setContactTel(e.target.value)}
          />
        </div>
      </Section>

      <Section title="Logistique">
        <div className="im-row" style={{ gap: 12 }}>
          <input
            className="im-input"
            placeholder="Prix (ex : Gratuit, 10€…)"
            value={prix}
            onChange={(e) => setPrix(e.target.value)}
          />
          <input
            className="im-input"
            type="number"
            min="0"
            placeholder="Capacité (nb de places)"
            value={capacite}
            onChange={(e) => setCapacite(e.target.value)}
          />
        </div>

        <label>
          Informations supplémentaires
          <textarea
            className="im-textarea"
            rows={6}
            placeholder="Accessibilité, matériel à prévoir…"
            value={infos}
            onChange={(e) => setInfos(e.target.value)}
          />
        </label>
      </Section>

      <div className="im-actions">
        <button className="im-btn" type="submit" disabled={busy}>
          {busy ? "Envoi…" : "Soumettre l’activité (relecture)"}
        </button>
        {msg ? <span style={{ color: "#0a7b34" }}>{msg}</span> : null}
        {err ? <span style={{ color: "#b00020" }}>{err}</span> : null}
      </div>
    </form>
  );
}
