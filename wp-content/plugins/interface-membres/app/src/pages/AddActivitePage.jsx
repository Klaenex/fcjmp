import React from "react";
import ActivitesForm from "../components/ActivitesForm";

export default function AddActivitePage() {
  return (
    <section style={{ display: "grid", gap: 12 }}>
      <ActivitesForm
        onCreated={() => {
          /* rafraîchir / toast / navigation */
        }}
      />
    </section>
  );
}
