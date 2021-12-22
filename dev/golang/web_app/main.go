package main

import (
	"encoding/json"
	"fmt"
	"net/http"
)

type person struct {
	Name string `json:"name"`
	Age  int    `json:"age"`
}

var tom *person = &person{
	Name: "Tom",
	Age:  28,
}

func fooHandler(w http.ResponseWriter, r *http.Request) {
	switch r.Method {
	case "GET":
		j, _ := json.Marshal(tom)
		w.Write(j)

	case "POST":
		d := json.NewDecoder(r.Body)
		p := &person{}
		err := d.Decode(p)
		if err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
		}
		tom = p

	default:
		w.WriteHeader(http.StatusMethodNotAllowed)
		fmt.Fprintf(w, "I can't do that")
	}
}

func homeHandler(w http.ResponseWriter, r *http.Request) {
	fmt.Fprint(w, "hello world!")
}

func barHandler(w http.ResponseWriter, r *http.Request) {
	name := r.URL.Query().Get("name")
	if name == "" {
		name = "localhost/bar?name=\"message!\""
	}
	fmt.Fprintf(w, "%s", name)
}

func main() {
	mux := http.NewServeMux()
	mux.HandleFunc("/", homeHandler)
	mux.HandleFunc("/bar", barHandler)
	mux.HandleFunc("/foo", fooHandler)

	http.ListenAndServe(":80", mux) // "", ":80"
}
