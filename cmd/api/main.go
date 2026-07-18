package main

import (
	"log"
	"net/http"
)

func main() {
	mux := http.NewServeMux()
	mux.HandleFunc("GET /health", checkHealth)

	log.Println("Starting server on :4000")
	log.Fatal(http.ListenAndServe(":4000", mux))
}

func checkHealth(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusOK)

	_, err := w.Write([]byte(`{"status":"ok"}`))
	if err != nil {
		log.Printf("write health response: %v", err)
	}
}
