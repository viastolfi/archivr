async function goTo(file="./index.php?action=error"){
    newScene = document.createElement("a-entity")
    newScene.setAttribute("id", "tmp")
    newScene.setAttribute('material', 'opacity', '0.5');
    baliseArray = document.querySelector("#base").childNodes
    baliseArray.forEach(element => {
        if(element.nodeName !== "#text")
        {
            element.emit("startanim",null,false)
        }
    });
    // Load NewScene's Content
    fetch(file)
        .then(response => response.text())
        .then(text => {
            newScene.innerHTML= text;
            document.querySelector("a-scene").append(newScene)
        });
    await new Promise(r => setTimeout(r, 1000));
    oldScene = document.querySelector("#base")
    oldScene.parentNode.removeChild(oldScene);
    newScene.setAttribute("id","base")
    newScene.childNodes.forEach(element  =>{
        if(element.nodeName !== "#text")
        {
            element.setAttribute("animation","property: opacity; from: 1.0; to: 0.0;startEvents: startanim; dur: 1000")
        }
    });
}


AFRAME.registerComponent('animationcustom', {
    init: async function () {
        this.el.setAttribute("animation","property: opacity; from: 0.0; to: 1.0; dur: 1000")
        if(this.el.parentNode.id === "base"){
            this.el.setAttribute("animation","property: opacity; from: 1.0; to: 0.0;startEvents: startanim; dur: 1000")
        }
    },
});


AFRAME.registerComponent('clickcontroller', {
    init: function () {
        this.el.addEventListener('triggerdown', this.logThumbstick);
    },
    logThumbstick: function (evt) {
        document.querySelector("a-box").setAttribute("color","red")
        evt.detail.el.getIntersection.setAttribute("color","purple")
    }
})
