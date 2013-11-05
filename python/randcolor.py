from PIL import Image, ImageDraw
import colorsys
import random

def randcolor(h = None, l = None, s = None):
    '''Generate a random nice color.'''
    if h is None:
        #Void solid red, green, blue
        h = random.uniform(0.02, 0.31) + random.choice([0, 1/3.0,2/3.0])
    else:
        h = h%(1/3.0)*0.29+0.02 + (int(h*3)/3.0)
    
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

edge = 30
size = (edge, edge)
im = Image.new('RGB', size, 'white')
draw = ImageDraw.Draw(im)

for i in xrange(edge):
    for j in xrange(edge):
        posl = (i, j)
        draw.rectangle((posl, posl), fill=randcolor())

del draw
im.show()