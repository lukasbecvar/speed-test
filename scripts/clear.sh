#!/bin/bash

# delete backend dependencies
sudo rm -rf vendor/
sudo rm -rf composer.lock

# delete frontend dependencies
sudo rm -rf package-lock.json
sudo rm -rf node_modules/

# delete frontend builded assets
sudo rm -rf public/bundles/
sudo rm -rf public/assets/

# delete symfony cache
sudo rm -rf var/

# delete docker services data
sudo rm -rf .docker/services/
