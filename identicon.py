from PIL import Image, ImageDraw
import hashlib
import colorsys
import random

def randcolor():
    h = random.uniform(0.02, 0.31)*random.choice([1,2,3])
    l = random.uniform(0.3, 0.8)
    s = random.uniform(0.3, 0.8)
    rgb = colorsys.hls_to_rgb(h, l, s)
    return (int(rgb[0]*256), int(rgb[1]*256), int(rgb[2]*256))

def gen_identicon(text):
    md5text = hashlib.md5(text).hexdigest() #hash the text
    size = (8,8)
    im = Image.new('RGB', size, 'white')
    draw = ImageDraw.Draw(im)
    c = randcolor()
    for i in xrange(len(md5text)):
        x, y = i%4, i/4
        posl = (x, y)
        posr = (7-x, y)
        if md5text[i] in '01234567':
            draw.rectangle((posl, posl), fill=c)
            draw.rectangle((posr, posr), fill=c)

    del draw
    return im
    
if __name__=='__main__':
    im = gen_identicon('')
    im.show()
    