app.initializers.add('chatsong-voice-note', () => {
    let mediaRecorder;
    let audioChunks = [];
    let isRecording = false;

    // Haal de Flarum Composer (reactiebalk) op
    const extendComposer = () => {
        const composerControls = document.querySelector('.Composer-controls');
        
        if (composerControls && !document.querySelector('.chatsong-voice-btn')) {
            // Maak de microfoonknop aan voor in de reactiebalk
            const btn = document.createElement('button');
            btn.className = 'Button Button--icon chatsong-voice-btn';
            btn.type = 'button';
            btn.innerHTML = '<i class="fas fa-microphone"></i>';
            btn.title = 'Spreek voice-note of demo in';
            
            // Actie wanneer de artiest op de knop tikt
            btn.onclick = async () => {
                if (!isRecording) {
                    try {
                        // Vraag om microfoontoegang op mobiel
                        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                        mediaRecorder = new MediaRecorder(stream);
                        audioChunks = [];

                        mediaRecorder.ondataavailable = (event) => {
                            audioChunks.push(event.data);
                        };

                        mediaRecorder.onstop = () => {
                            const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                            const audioUrl = URL.createObjectURL(audioBlob);
                            
                            // Voeg de audio-link automatisch in het Flarum tekstveld in
                            const textarea = document.querySelector('.TextEditor-editor textarea, textarea');
                            if (textarea) {
                                textarea.value += `\n🎵 [Voice Note / Audio Demo](${audioUrl})\n`;
                                textarea.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                            alert('🎙️ Je audio-opname is succesvol toegevoegd aan je bericht!');
                        };

                        mediaRecorder.start();
                        isRecording = true;
                        btn.style.color = '#ffffff';
                        btn.style.background = '#e11d48';
                        btn.title = 'Tik om opname te stoppen';
                    } catch (err) {
                        console.error('Microfoontoegang fout:', err);
                        alert('Kan geen toegang krijgen tot de microfoon. Controleer je browserrechten.');
                    }
                } else {
                    // Stop de opname
                    mediaRecorder.stop();
                    isRecording = false;
                    btn.style.color = '#e11d48';
                    btn.style.background = 'rgba(225, 29, 72, 0.1)';
                    btn.title = 'Spreek voice-note of demo in';
                }
            };

            // Voeg de knop toe aan de knoppenbalk van de composer
            composerControls.appendChild(btn);
        }
    };

    // Luister naar het openen van de composer
    setInterval(extendComposer, 500);
});
