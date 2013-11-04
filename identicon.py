'''
Usage: python identicon.py [text]
'''
from PIL import Image, ImageDraw
import hashlib
import colorsys
import random

def gen_color(h = None, l = None, s = None):
    '''Generate a random nice color.'''
    if h is None:
        #Void solid red, green, blue
        h = random.uniform(0.02, 0.31)*random.choice([1,2,3])
    else:
        h = (h*0.29+0.02)*(int(h*3)+1)
    
    if l is None:
        #Void too dark or too light
        l = random.uniform(0.3, 0.8)
    else:
        l = l*0.5+0.3
    
    if s is None:
        #Void too dark or too bright
        s = random.uniform(0.3, 0.8)
    else:
        s = s*0.5+0.3

    rgb = colorsys.hls_to_rgb(h, l, s)
    return (int(rgb[0]*256), int(rgb[1]*256), int(rgb[2]*256))

def gen_identicon(text):
    md5text = hashlib.md5(text).hexdigest() #hash the text
    size = (8,8)  #image size
    im = Image.new('RGB', size, 'white')
    draw = ImageDraw.Draw(im)
    
    #get the color of the icon
    h = int(md5text[0:16], 16)%1000/1000.0
    l = int(md5text[16:24], 16)%1000/1000.0
    s = int(md5text[24:32], 16)%1000/1000.0
    color = gen_color(h, l, s)
    
    for i in xrange(len(md5text)):
        x, y = i%4, i/4
        posl = (x, y)
        posr = (7-x, y)
        if md5text[i] in '01234567':
            draw.rectangle((posl, posl), fill=color)
            draw.rectangle((posr, posr), fill=color)

    del draw
    return im
    
if __name__=='__main__':
    import sys, os
    text = 'maogm12@gmail.com'
    if len(sys.argv) >= 2:
        text = sys.argv[1]
    im = gen_identicon(text)
    im.show()