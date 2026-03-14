// settings servers
let arr = document.querySelectorAll(".backup-control .servers .server")
arr.forEach((e)=> {
    e.onclick = function() {
        toggleServer(e)
    }
})
function toggleServer(element) {
    arr.forEach((el) => {
        el.classList.remove("clicked")
    })
    element.classList.add("clicked")
}