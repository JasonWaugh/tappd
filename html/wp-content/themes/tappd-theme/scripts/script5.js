var card_id = document.getElementsByClassName("acf-field-635ffb5ce730f");
// card_id.style.display = "none";
//document.getElementsByClassName("acf-field-635ffb5ce730f").style.visibility = "hidden";
//document.getElementsByClassName("acf-field-635ffb5ce730f").style.display = "none";

const urlParams = new URLSearchParams(window.location.search);
const myParam = urlParams.get('cardID');

console.log(myParam);
document.getElementById("acf-field_635ffb5ce730f").value = myParam;
