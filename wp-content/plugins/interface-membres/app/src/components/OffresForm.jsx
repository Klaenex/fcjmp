import React from "react";
import { cfg } from "../config";
import { createOffre } from "../api/content";

/* Options exemples — adapte si tu as des listes officielles */
const TYPES_OFFRE = ["CDI", "CDD", "Stage", "Volontariat", "Autre"];
const REGIMES = ["Temps plein", "Mi-temps", "4/5ème", "Autre"];
const ZONES = [
  "Bruxelles",
  "Wallonie",
  "Flandre",
  "National",
  "Remote",
  "Autre",
];

export default function OffresForm({ onCreated }) {
  const [busy, setBusy] = React.useState(false);
  const [msg, setMsg] = React.useState("");
  const [err, setErr] = React.useState("");

  const [title, setTitle] = React.useState("");
  const [typeOffre, setTypeOffre] = React.useState("");
  const [typePrec, setTypePrec] = React.useState("");
  const [regime, setRegime] = React.useState("");
  const [regimePrec, setRegimePrec] = React.useState("");
  const [zone, setZone] = React.useState("");
  const [lieuPrec, setLieuPrec] = React.useState("");

  const [descAsbl, setDescAsbl] = React.useState("");
  const [descPoste, setDescPoste] = React.useState("");
  const [missions, setMissions] = React.useState("");
  const [qualifs, setQualifs] = React.useState("");
  const [competences, setCompetences] = React.useState("");

  const [conditions, setConditions] = React.useState("");
  const [infos, setInfos] = React.useState("");
  const [urlCandidature, setUrlCandidature] = React.useState("");
  const [emailCandidature, setEmailCandidature] = React.useState("");
  const [telCandidature, setTelCandidature] = React.useState("");
  const [dateLimite, setDateLimite] = React.useState("");

  function section(title, children) {
    return (
      <div className="im-card" style={{ display: "grid", gap: 12 }}>
        <h3>{title}</h3>
        {children}
      </div>
    );
  }

  function validate() {
    if (!title.trim()) return "L’intitulé du poste est obligatoire.";
    if (!typeOffre) return "Le type d’offre est obligatoire.";
    if (!regime) return "Le régime est obligatoire.";
    if (!zone) return "La zone d’action est obligatoire.";
    if (!descPoste.trim()) return "La description du poste est obligatoire.";
    if (!missions.trim())
      return "Le champ « Missions & Responsabilités » est obligatoire.";
    if (!qualifs.trim())
      return "Le champ « Qualification et expérience » est obligatoire.";
    if (!competences.trim())
      return "Le champ « Compétences requises » est obligatoire.";
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

    const meta = {
      im_off_type: typeOffre || "",
      im_off_type_prec: typePrec || "",
      im_off_regime: regime || "",
      im_off_regime_prec: regimePrec || "",
      im_off_zone: zone || "",
      im_off_lieu_prec: lieuPrec || "",
      im_off_desc_asbl: descAsbl || "",
      im_off_desc_poste: descPoste || "",
      im_off_missions: missions || "",
      im_off_qualifs: qualifs || "",
      im_off_competences: competences || "",
      im_off_conditions: conditions || "",
      im_off_infos: infos || "",
      im_off_candidature_url: urlCandidature || "",
      im_off_candidature_email: emailCandidature || "",
      im_off_candidature_tel: telCandidature || "",
      im_off_date_limite: dateLimite || "",
    };

    const payload = {
      title,
      content: descPoste,
      meta,
      status: cfg.status.pending, // toujours en relecture
    };

    setBusy(true);
    try {
      await createOffre(payload);
      setMsg("Offre soumise pour validation ✅");
      setErr("");
      setTitle("");
      setTypeOffre("");
      setTypePrec("");
      setRegime("");
      setRegimePrec("");
      setZone("");
      setLieuPrec("");
      setDescAsbl("");
      setDescPoste("");
      setMissions("");
      setQualifs("");
      setCompetences("");
      setConditions("");
      setInfos("");
      setUrlCandidature("");
      setEmailCandidature("");
      setTelCandidature("");
      setDateLimite("");
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
      <h2>Nouvelle offre d’emploi</h2>

      {section(
        "Intitulé du poste",
        <input
          className="im-input"
          placeholder="ex : Chargé·e d’accueil..."
          value={title}
          onChange={(e) => setTitle(e.target.value)}
        />
      )}

      {section(
        "Informations importantes pour classer l’offre",
        <>
          <div className="im-row">
            <label style={{ minWidth: 160 }}>Type d’offre *</label>
            <select
              className="im-input"
              value={typeOffre}
              onChange={(e) => setTypeOffre(e.target.value)}
            >
              <option value="">Sélectionner</option>
              {TYPES_OFFRE.map((t) => (
                <option key={t} value={t}>
                  {t}
                </option>
              ))}
            </select>
          </div>

          <input
            className="im-input"
            placeholder="Précision type d’offre (APE, ACS, …)"
            value={typePrec}
            onChange={(e) => setTypePrec(e.target.value)}
          />

          <div className="im-row">
            <label style={{ minWidth: 160 }}>Régime *</label>
            <select
              className="im-input"
              value={regime}
              onChange={(e) => setRegime(e.target.value)}
            >
              <option value="">Sélectionner</option>
              {REGIMES.map((r) => (
                <option key={r} value={r}>
                  {r}
                </option>
              ))}
            </select>
          </div>

          <input
            className="im-input"
            placeholder="Précision régime (ex : 4/5ème)"
            value={regimePrec}
            onChange={(e) => setRegimePrec(e.target.value)}
          />

          <div className="im-row">
            <label style={{ minWidth: 160 }}>Zone d’action *</label>
            <select
              className="im-input"
              value={zone}
              onChange={(e) => setZone(e.target.value)}
            >
              <option value="">Sélectionner</option>
              {ZONES.map((z) => (
                <option key={z} value={z}>
                  {z}
                </option>
              ))}
            </select>
          </div>

          <input
            className="im-input"
            placeholder="Précision lieu (Ville, quartier…)"
            value={lieuPrec}
            onChange={(e) => setLieuPrec(e.target.value)}
          />
        </>
      )}

      {section(
        "Description de l’asbl et du poste",
        <>
          <label>Description de l’asbl</label>
          <textarea
            className="im-textarea"
            rows={6}
            placeholder="Présentez brièvement l’organisation"
            value={descAsbl}
            onChange={(e) => setDescAsbl(e.target.value)}
          />

          <label>Description du poste *</label>
          <textarea
            className="im-textarea"
            rows={8}
            placeholder="Décrivez le poste…"
            value={descPoste}
            onChange={(e) => setDescPoste(e.target.value)}
          />
        </>
      )}

      {section(
        "Missions & Responsabilités *",
        <textarea
          className="im-textarea"
          rows={8}
          placeholder="Liste ou texte libre…"
          value={missions}
          onChange={(e) => setMissions(e.target.value)}
        />
      )}

      {section(
        "Qualification et expérience *",
        <textarea
          className="im-textarea"
          rows={6}
          placeholder="Diplômes, expériences…"
          value={qualifs}
          onChange={(e) => setQualifs(e.target.value)}
        />
      )}

      {section(
        "Compétences requises *",
        <textarea
          className="im-textarea"
          rows={6}
          placeholder="Compétences techniques/comportementales…"
          value={competences}
          onChange={(e) => setCompetences(e.target.value)}
        />
      )}

      {section(
        "Offre & Conditions de travail",
        <textarea
          className="im-textarea"
          rows={6}
          placeholder="Rémunération, horaires, avantages…"
          value={conditions}
          onChange={(e) => setConditions(e.target.value)}
        />
      )}

      {section(
        "Informations supplémentaires",
        <textarea
          className="im-textarea"
          rows={6}
          placeholder="Tout autre détail utile…"
          value={infos}
          onChange={(e) => setInfos(e.target.value)}
        />
      )}

      {section(
        "Postuler / S’inscrire",
        <>
          <input
            className="im-input"
            placeholder="Lien de candidature (URL)"
            value={urlCandidature}
            onChange={(e) => setUrlCandidature(e.target.value)}
          />
          <input
            className="im-input"
            placeholder="Email de contact"
            value={emailCandidature}
            onChange={(e) => setEmailCandidature(e.target.value)}
          />
          <input
            className="im-input"
            placeholder="Téléphone"
            value={telCandidature}
            onChange={(e) => setTelCandidature(e.target.value)}
          />
          <div className="im-row">
            <label style={{ minWidth: 160 }}>Date limite</label>
            <input
              className="im-input"
              type="date"
              value={dateLimite}
              onChange={(e) => setDateLimite(e.target.value)}
            />
          </div>
        </>
      )}

      <div className="im-actions">
        <button className="im-btn" type="submit" disabled={busy}>
          {busy ? "Envoi…" : "Soumettre l’offre (relecture)"}
        </button>
        {msg ? <span style={{ color: "#0a7b34" }}>{msg}</span> : null}
        {err ? <span style={{ color: "#b00020" }}>{err}</span> : null}
      </div>
    </form>
  );
}
