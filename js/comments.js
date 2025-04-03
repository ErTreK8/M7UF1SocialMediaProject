document.addEventListener("DOMContentLoaded", function () {
    // Manejar la adición de comentarios
    document.querySelectorAll(".comment-form").forEach(form => {
        form.addEventListener("submit", function (e) {
            e.preventDefault();

            let postId = this.getAttribute("data-postid");
            let commentInput = this.querySelector("input[name='comentario']");
            let commentText = commentInput.value.trim();
            let commentsContainer = document.getElementById("comments-" + postId);

            if (commentText === "") {
                alert("El comentario no puede estar vacío.");
                return;
            }

            fetch("../php/posts/comment.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `idPost=${postId}&comentario=${encodeURIComponent(commentText)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Crear un nuevo comentario con el nombre del usuario
                    let newComment = document.createElement("div");
                    newComment.classList.add("comment");

                    let userSpan = document.createElement("strong");
                    userSpan.textContent = data.nomUsari + ": "; // Nombre del usuario

                    let commentTextElement = document.createElement("span");
                    commentTextElement.textContent = data.comentario; // Texto del comentario

                    // Crear el botón de like
                    let likeButton = document.createElement("button");
                    likeButton.classList.add("like-btn");
                    likeButton.setAttribute("data-tipo", "comentario");
                    likeButton.setAttribute("data-id", ""); // ID del comentario (se llenará después)
                    likeButton.textContent = "🤍 Dar Like (0)";

                    // Agregar elementos al nuevo comentario
                    newComment.appendChild(userSpan);
                    newComment.appendChild(commentTextElement);
                    newComment.appendChild(likeButton);

                    // Agregar el nuevo comentario al contenedor
                    commentsContainer.prepend(newComment);

                    // Limpiar el campo de texto
                    commentInput.value = "";

                    // Obtener el ID del comentario recién creado
                    fetch("../php/posts/get_last_comment_id.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `idPost=${postId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            let commentId = data.idComentari;
                            likeButton.setAttribute("data-id", commentId); // Asignar el ID del comentario
                        } else {
                            console.error("Error al obtener el ID del comentario:", data.error);
                        }
                    })
                    .catch(error => console.error("Error:", error));
                } else {
                    alert(data.error);
                }
            })
            .catch(error => console.error("Error:", error));
        });
    });

    // Delegación de eventos para los botones de like
    document.querySelectorAll(".comments").forEach(commentsContainer => {
        commentsContainer.addEventListener("click", function (e) {
            // Verificar si el clic fue en un botón de like
            let button = e.target.closest(".like-btn");
            if (!button) return;

            let tipo = button.getAttribute("data-tipo");
            let idObjeto = button.getAttribute("data-id");

            if (!tipo || !idObjeto) return;

            // Enviar solicitud al backend para procesar el like
            fetch("../php/posts/like.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `tipo=${tipo}&idObjeto=${idObjeto}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar el texto del botón
                    const totalLikes = parseInt(button.textContent.match(/\d+/)[0]);
                    if (data.accion === 'agregar') {
                        button.textContent = `❤️ Quitar Like (${totalLikes + 1})`;
                    } else {
                        button.textContent = `🤍 Dar Like (${totalLikes - 1})`;
                    }
                } else {
                    alert(data.error);
                }
            })
            .catch(error => console.error("Error:", error));
        });
    });
});