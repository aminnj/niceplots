#!/bin/bash

if [ $# -lt 1 ]; then
    echo "usage: $(basename $BASH_SOURCE) <folder name>"
    return 1
fi

dir=$1
outdir=$1

if [ $# -gt 1 ]; then 
    echo "Will put the stuff in $outdir instead"
    outdir=$2;
fi

echo "$(date) $(pwd) $dir $outdir" >> ~/.niceplots_history.txt

function pdftopng {
    #sharpen not really necessary
    #convert -density 250 -trim $1 -quality 100 -sharpen 0x1.0 ${1%%.pdf}.png
    if [ $# -gt 0 ]; then
        density=125
        if [ $# -gt 1 ]; then
            density=$2
        fi
        echo "$1 ==> ${1%%.pdf}.png" 
        gs -q -sDEVICE=pngalpha -o ${1%%.pdf}.png -sDEVICE=pngalpha -dUseCropBox -r${density} $1
        # convert -density ${density} -trim $1 -fuzz 1% ${1%%.pdf}.png
        fi
    else
        echo "Usage: pdftopng <pdf name> [optional density]"
    fi
}
export -f pdftopng

if [ -z $NOCONVERT ]; then
    # ls -1 ${dir}/*.pdf | xargs -I%  -n 1 -P 20 sh -c "pdftopng % 75;"
    # ls -1 ${dir}/*/*.pdf | xargs -I%  -n 1 -P 20 sh -c "pdftopng % 75;"
    ls -1 ${dir}/*.pdf | xargs -I%  -n 1 -P 20 sh -c "pdftopng % 150;"
    ls -1 ${dir}/*/*.pdf | xargs -I%  -n 1 -P 20 sh -c "pdftopng % 150;"
else
    echo "Not converting anything"
fi

index=$HOME/syncfiles/miscfiles/index.php

ln -s $index $dir/index.php


HOST=my.host.edu

# wait
chmod -R a+r $dir
mkdir -p ~/public_html/dump/plots/$outdir/
if [[ "$NOINTERNET" == "true" ]]; then
    cp -rp $dir/* ~/public_html/dump/plots/$outdir/
else
    ssh $USER@$HOST "mkdir -p ~/public_html/dump/plots/$outdir; rm ~/public_html/dump/plots/$outdir/*.png"
    scp -rp $dir/* $USER@$HOST:~/public_html/dump/plots/$outdir/
fi
# echo "${HOSTNAME}/~$USER/dump/$outdir/"
echo "$HOST/~$USER/dump/plots/$outdir/"
