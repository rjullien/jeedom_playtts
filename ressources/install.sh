echo "********************************************************"
echo "*             Installation des dépendances             *"
echo "********************************************************"
sudo apt-get update  -y -q
sudo apt-get install -y python-pip python-dev mplayer mpg123 lsb-release
test=`lsb_release -is`
if [ "$test" = "Raspbian" ]; then
        sudo apt-get install -y libsox-fmt-mp3 sox libttspico-data
else
        sudo apt-get install -y libsox-fmt-mp3 sox libttspico-utils
fi
sudo pip install gTTS
sudo pip install requests
sudo chown -R www-data:www-data /usr/share/nginx/www/jeedom/plugins/playtts
sudo chmod -R 755 /usr/share/nginx/www/jeedom/plugins/playtts
sudo usermod -a -G audio www-data
sudo amixer set Master 85%
sudo amixer set Headphone 85%
sudo amixer set PCM 85%


echo "********************************************************"
echo "*             Installation terminée                    *"
echo "********************************************************"
sleep 3
sudo /etc/init.d/nginx restart
