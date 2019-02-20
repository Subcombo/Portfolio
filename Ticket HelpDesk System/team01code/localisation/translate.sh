# usage example: ./translate.sh ar phrase_list.txt > ar.part.lang
while IFS= read -r p; do
  PYTHONIOENCODING=utf-8 ./translate.py --lang en $1 --quietly --text "$p"
done <$2