document.addEventListener("DOMContentLoaded", function () {
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
                    let newComment = document.createElement("p");
                    newComment.textContent = commentText;
                    commentsContainer.prepend(newComment);
                    commentInput.value = ""; // Limpiar el campo de texto
                } else {
                    alert(data.error);
                }
            })
            .catch(error => console.error("Error:", error));
        });
    });
});
