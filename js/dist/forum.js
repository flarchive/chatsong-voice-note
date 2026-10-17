define('chatsong/voice-note', ['flarum/app'], function(app) {
    app.initializers.add('chatsong/voice-note', function() {
        const observer = new MutationObserver((mutations, obs) => {
            // Zoekt zowel de standaard Flarum toolbar als de flatrateComposer toolbar
            const toolbar = document.querySelector('.TextEditor-toolbar, .flatrateComposer-toolbar, .Composer-footer');
            if (toolbar && !document.querySelector('.chatsong-voice-btn')) {
                createVoiceButton(toolbar);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });

    function createVoiceButton(toolbar) {
        // Maak een lijst-item aan omdat jouw toolbar met <ul> werkt
        const li = document.createElement('li');
        li.className = 'Composer-control';

        const btn = document.createElement('button');
        btn.className = 'Button Button--icon chatsong-voice-btn';
        btn.type = 'button';
        btn.innerHTML = '<i class="fas fa-microphone"></i>';
        btn.title = 'Spreek een voice-note in';

        btn.style.color = '#e11d48';
        btn.style.background = 'rgba(225, 29, 72, 0.1)';
        btn.style.border = 'none';
        btn.style.borderRadius = '4px';
        btn.style.padding = '6px 10px';
        btn.style.cursor = 'pointer';
        btn.style.marginRight = '5px';

        let mediaRecorder;
        let audioChunks = [];
        let isRecording = false;

        btn.onclick = async () => {
            const textarea = document.querySelector('.TextEditor-editor textarea');
            if (!textarea) return;

            if (!isRecording) {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    mediaRecorder = new MediaRecorder(stream);
                    audioChunks = [];

                    mediaRecorder.ondataavailable = (e) => audioChunks.push(e.data);

                    mediaRecorder.onstop = () => {
                        const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                        const audioUrl = URL.createObjectURL(audioBlob);
                        
                        const start = textarea.selectionStart;
                        const text = textarea.value;
                        textarea.value = text.substring(0, start) + `\n🎵 [Voice Note / Audio Demo](${audioUrl})\n` + text.substring(textarea.selectionEnd);
                        textarea.dispatchEvent(new Event('input', { bubbles: true }));
                    };

                    mediaRecorder.start();
                    isRecording = true;
                    btn.style.color = '#ffffff';
                    btn.style.background = '#e11d48';
                    btn.title = 'Klik om opname te stoppen';
                } catch (err) {
                    console.error('Microfoontoegang geweigerd:', err);
                    alert('Geen toegang tot de microfoon. Controleer je browserrechten.');
                }
            } else {
                mediaRecorder.stop();
                isRecording = false;
                btn.style.color = '#e11d48';
                btn.style.background = 'rgba(225, 29, 72, 0.1)';
                btn.title = 'Spreek een voice-note in';
            }
        };

        li.appendChild(btn);
        toolbar.prepend(li);
    }
});
