<?php

use Flarum\Extend;

return [
    (new Extend\Frontend('forum'))
        ->content(function ($document) {
            $document->foot[] = '
            <script>
            (function() {
                // Directe MutationObserver die continu en live de DOM in de gaten houdt
                const observer = new MutationObserver(function(mutations) {
                    // Zoek naar de exacte toolbars uit jouw HTML-structuur
                    const toolbar = document.querySelector(".TextEditor-controls, .TextEditor-toolbar");
                    
                    if (toolbar && !document.querySelector(".chatsong-voice-btn")) {
                        const btn = document.createElement("button");
                        btn.className = "Button Button--icon chatsong-voice-btn hasIcon";
                        btn.type = "button";
                        btn.innerHTML = "<i class=\"icon fas fa-microphone Button-icon\" style=\"color:#e11d48;\"></i><span class=\"Button-label\" style=\"color:#e11d48;\">Voice</span>";
                        btn.title = "Spreek een voice-note in";
                        btn.style.cssText = "background: rgba(225,29,72,0.1); border: 1px solid rgba(225,29,72,0.3); border-radius: 4px; padding: 4px 8px; margin-right: 5px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;";

                        let mediaRecorder;
                        let audioChunks = [];
                        let isRecording = false;

                        btn.onclick = async (e) => {
                            e.preventDefault();
                            const textarea = document.querySelector(".TextEditor-editor");
                            if (!textarea) return;

                            if (!isRecording) {
                                try {
                                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                                    mediaRecorder = new MediaRecorder(stream);
                                    audioChunks = [];
                                    
                                    mediaRecorder.ondataavailable = event => audioChunks.push(event.data);
                                    
                                    mediaRecorder.onstop = () => {
                                        const audioBlob = new Blob(audioChunks, { type: "audio/webm" });
                                        const audioUrl = URL.createObjectURL(audioBlob);
                                        
                                        // Plak de audiolink direct in de tekstbewerker
                                        textarea.value += "\\n🎵 [Voice Note](" + audioUrl + ")\\n";
                                        textarea.dispatchEvent(new Event("input", { bubbles: true }));
                                    };

                                    mediaRecorder.start();
                                    isRecording = true;
                                    btn.style.background = "#e11d48";
                                    btn.querySelector("i, span").style.color = "#ffffff";
                                    btn.title = "Klik om opname te stoppen";
                                } catch (err) {
                                    alert("Geen toegang tot de microfoon. Controleer jebrowserrechten.");
                                }
                            } else {
                                mediaRecorder.stop();
                                isRecording = false;
                                btn.style.background = "rgba(225,29,72,0.1)";
                                btn.querySelectorAll("i, span").forEach(el => el.style.color = "#e11d48");
                                btn.title = "Spreek een voice-note in";
                            }
                        };

                        // Voeg de knop netjes toe aan het begin van de toolbar
                        toolbar.prepend(btn);
                    }
                });

                // Start direct met observeren
                observer.observe(document.body, { childList: true, subtree: true });
            })();
            </script>';
        }),
];
