DEV_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))/../mailguardev

.PHONY: loadtest stoptest logs setup

# Avvia l'ambiente di test Flarum + MailGuard
loadtest:
	@echo "🚀 Avvio ambiente Flarum + MailGuard..."
	docker compose -f $(DEV_DIR)/docker-compose.yml up -d --build
	@echo ""
	@echo "✅ Container avviati! Ora esegui il setup:"
	@echo "   make setup"
	@echo ""
	@echo "Oppure se hai già fatto il setup: http://localhost:8080"

# Setup Flarum + installa estensione (eseguire solo la prima volta o dopo reset)
setup:
	@echo "⚙️  Setup Flarum + MailGuard nel container..."
	docker cp $(DEV_DIR)/docker/setup.sh flarum-dev:/tmp/setup.sh
	docker exec -it flarum-dev bash /tmp/setup.sh

# Ferma e pulisci tutto
stoptest:
	@echo "🛑 Fermo ambiente di test..."
	docker compose -f $(DEV_DIR)/docker-compose.yml down
	@echo "✅ Fermato."

# Ferma e cancella anche i volumi (reset totale)
resettest:
	@echo "💣 Reset totale (cancella DB e dati Flarum)..."
	docker compose -f $(DEV_DIR)/docker-compose.yml down -v
	@echo "✅ Tutto cancellato."

# Mostra i log live
logs:
	docker compose -f $(DEV_DIR)/docker-compose.yml logs -f
