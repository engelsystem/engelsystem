;; Any site-wide Festival initialization can be added to this file.
;; It is marked as a configuration file, so your changes will be saved
;; across upgrades of the Festival package.
;;


; Server access list (hosts)
(set! server_access_list '("[^.]+" "127.0.0.1" "localhost.*"))

;; Command for Asterisk begin
(define (tts_textasterisk string mode)
	"(tts_textasterisk STRING MODE)
	Apply tts to STRING.  This function is specifically designed for
	use in server mode so a single function call may synthesize the string.
	This function name may be added to the server safe functions."
	(utt.send.wave.client (utt.wave.resample (utt.wave.rescale (utt.synth (eval (list 'Utterance 'Text string))) 5) 8000)))

