import React from "react";
import OffresForm from "../components/OffresForm";

export default function AddOffrePage() {
  return (
    <section style={{ display: "grid", gap: 12 }}>
      <OffresForm
        onCreated={() => {
          /* rafraîchir / toast / navigation */
        }}
      />
    </section>
  );
}
