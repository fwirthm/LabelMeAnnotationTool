#!/bin/sh

#  ClearAllAnnotations.sh
#  
#
#  Created by Florian Wirthmüller on 16.11.16.
#

echo "the following files will be removed:"

for f in $(find ./Annotations ./AnnotationsVOC ./Masks ./Scribbles -name '*.xml' -or -name '*.png');
do echo "$f"; done


select yn in "Yes" "No"; do
case $yn in
    Yes ) echo "files will be removed:";

    for f in $(find ./Annotations ./AnnotationsVOC ./Masks ./Scribbles -name '*.xml' -or -name '*.png');
    do
        rm $f;
        echo "$f removed succesfull";
    done

    break;;
    No ) echo "abort"; exit;;
esac
done

