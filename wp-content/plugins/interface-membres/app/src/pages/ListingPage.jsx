import React from "react";
import MyListing from "../components/MyListing";

export default function ListingPage() {
  return (
    <section style={{ display: "grid", gap: 12 }}>
      <MyListing />
    </section>
  );
}
