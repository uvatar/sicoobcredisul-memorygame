document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.card:not(.fixed)');
    const flipsCounter = document.getElementById('flips');
    const nextButton = document.querySelector('.next-button-container');
    const gameMessage = document.getElementById('game-message');
    
    let flips = 0;
    let hasFlippedCard = false;
    let lockBoard = false;
    let gameOver = false;
    let firstCard, secondCard;
    let matchedPairs = 0;
    
    
    const images = ['1.png', '2.png', '3.png', '4.png', '1.png', '2.png', '3.png', '4.png'];
    
    
    function shuffleImages() {
        for (let i = images.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [images[i], images[j]] = [images[j], images[i]];
        }
    }
    
    
    function initGame() {
        shuffleImages();
        
        
        cards.forEach((card, index) => {
            card.innerHTML = `
                <div class="card-inner">
                    <div class="card-front"></div>
                    <div class="card-back">
                        <img src="images/${images[index]}" alt="Card Image" class="card-image">
                    </div>
                </div>
            `;
            card.addEventListener('click', flipCard);
            card.classList.add('game-card'); 
        });
    }
    
    function flipCard() {
        if (lockBoard) return;
        if (gameOver) return;
        if (this === firstCard) return;
        
        this.classList.add('flipped');
        flips++;
        flipsCounter.textContent = flips;
        
        
        document.getElementById('flips_count_input').value = flips;
        
        
        if (flips >= MAX_FLIPS) {
            endGame(false);
        }
        
        
        fetch('update_flips.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ flips: flips })
        });
        
        if (!hasFlippedCard) {
            
            hasFlippedCard = true;
            firstCard = this;
            return;
        }
        
        
        secondCard = this;
        checkForMatch();
    }
    
    function checkForMatch() {
        
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
        
        
        if (matchedPairs === 4) {
            endGame(true);
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
    
    function endGame(won) {
        gameOver = true;
        
        
        document.getElementById('game_won_input').value = won ? '1' : '0';
        
        if (!won) {
            
            cards.forEach(card => {
                if (!card.classList.contains('fixed')) {
                    card.classList.add('game-over-card');
                }
            });

            nextButton.style.display = 'block';
        }
            
            




            




        
        



    }
    
    
    initGame();
});