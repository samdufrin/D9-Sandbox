(function (Drupal, $, once){
    const tweets = document.querySelectorAll('.twitter-tweet');

    tweets.forEach((tweet) => {
        const clickableElements = Array.from(tweet.querySelectorAll('a'));
        const link = tweet.querySelector('.tweet-link');

        clickableElements.forEach((element) => {
            element.addEventListener('click', (e) => e.stopPropagation());
        });

        tweet.addEventListener("click", () => {
            const noTextSelected = !window.getSelection().toString();
            if (noTextSelected) {
                link.click();
            }
        });
    });
})(Drupal, $, once);

(function (Drupal, $, once){
    const posts = document.querySelectorAll('.facebook-post');

    posts.forEach((post) => {
        const clickableElements = Array.from(post.querySelectorAll('a'));
        const link = post.querySelector('.fb-post-link');

        clickableElements.forEach((element) => {
            element.addEventListener('click', (e) => e.stopPropagation());
        });

        post.addEventListener("click", () => {
            const noTextSelected = !window.getSelection().toString();
            if (noTextSelected) {
                link.click();
            }
        });
    });
})(Drupal, $, once);