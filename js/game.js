document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.card:not(.fixed)');
    const flipsCounter = document.getElementById('flips');
    const nextButton = document.querySelector('.next-button-container');
    
    let flips = 0;
    let hasFlippedCard = false;
    let lockBoard = false;
    let firstCard, secondCard;
    let matchedPairs = 0;
    
    // Card content (4 pairs of images)
    const images = ['1.png', '2.png', '3.png', '4.png', '1.png', '2.png', '3.png', '4.png'];
    
    // Shuffle images (Fisher-Yates algorithm)
    function shuffleImages() {
        for (let i = images.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [images[i], images[j]] = [images[j], images[i]];
        }
    }
    
    // Initialize game
    function initGame() {
        shuffleImages();
        
        // Create card HTML structure
        cards.forEach((card, index) => {
            card.innerHTML = `
                <div class="card-inner">
                    <div class="card-front"></div>
                    <div class="card-back">
                        <img src="images/${images[index]}" alt="Card Image">
                    </div>
                </div>
            `;
            card.addEventListener('click', flipCard);
        });
    }
    
    function flipCard() {
        if (lockBoard) return;
        if (this === firstCard) return;
        
        this.classList.add('flipped');
        flips++;
        flipsCounter.textContent = flips;
        
        // Update hidden input with current flips count
        document.getElementById('flips_count_input').value = flips;
        
        // Still send to server as backup
        fetch('update_flips.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ flips: flips })
        });
        
        if (!hasFlippedCard) {
            // First card flipped
            hasFlippedCard = true;
            firstCard = this;
            return;
        }
        
        // Second card flipped
        secondCard = this;
        checkForMatch();
    }
    
    function checkForMatch() {
        // Check if the image sources match
        const firstCardImage = firstCard.querySelector('.card-back img').src;
        const secondCardImage = secondCard.querySelector('.card-back img').src;
        
        const isMatch = firstCardImage === secondCardImage;
        
        isMatch ? disableCards() : unflipCards();
    }
    
    function disableCards() {
        firstCard.removeEventListener('click', flipCard);
        secondCard.removeEventListener('click', flipCard);
        
        firstCard.classList.add('matched');
        secondCard.classList.add('matched');
        
        matchedPairs++;
        
        // Check if all pairs are matched
        if (matchedPairs === 4) {
            setTimeout(() => {
                nextButton.style.display = 'block';
            }, 1000);
        }
        
        resetBoard();
    }
    
    function unflipCards() {
        lockBoard = true;
        
        setTimeout(() => {
            firstCard.classList.remove('flipped');
            secondCard.classList.remove('flipped');
            
            resetBoard();
        }, 1000);
    }
    
    function resetBoard() {
        [hasFlippedCard, lockBoard] = [false, false];
        [firstCard, secondCard] = [null, null];
    }
    
    // Initialize game
    initGame();
});