<script>
    let subMenu = document.getElementById("subMenu");

    function toggleMenu() {
        subMenu.classList.toggle("open-menu");
    }

    document.querySelectorAll('.like-button').forEach(button => {
        button.addEventListener('click', function () {
            var action = this.getAttribute('data-action');
            var postId = this.closest('.like-form').getAttribute('data-post-id');
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'toggle_like.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    button.textContent = response.action === 'like' ? 'ยกเลิกไลค์' : 'ไลค์';
                    button.setAttribute('data-action', response.action);
                    button.closest('.like-form').nextElementSibling.textContent = 'จำนวนไลค์: ' + response.like_count;
                }
            };
            xhr.send('post_id=' + postId + '&action=' + action);
        });
    });
</script>
<script>
    document.querySelectorAll('.like-form').forEach(form => {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            const postId = this.dataset.postId;
            const action = this.querySelector('button').value;

            fetch('toggle_like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    post_id: postId,
                    action: action
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.action === 'unlike') {
                        this.querySelector('button').textContent = 'ยกเลิกไลค์';
                        this.querySelector('button').value = 'unlike';
                    } else {
                        this.querySelector('button').textContent = 'ไลค์';
                        this.querySelector('button').value = 'like';
                    }
                    this.nextElementSibling.textContent = 'จำนวนไลค์: ' + data.like_count;
                });
        });
    });
</script>
</body>

</html>