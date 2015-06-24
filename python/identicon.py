'''
identicon.py
maogm12@gmail.com
Usage: python identicon.py [text]
'''
from PIL import Image, ImageDraw
import hashlib
import colorsys
import random

def murmurhash( key, seed = 0x0 ):
    ''' implements 32bit murmur3 hash. '''
    key = bytearray( key )

    def fmix( h ):
        h ^= h >> 16
        h  = ( h * 0x85ebca6b ) & 0xFFFFFFFF
        h ^= h >> 13
        h  = ( h * 0xc2b2ae35 ) & 0xFFFFFFFF
        h ^= h >> 16
        return h;

    length = len( key )
    nblocks = length / 4

    h1 = seed;

    c1 = 0xcc9e2d51
    c2 = 0x1b873593

    # body
    for block_start in xrange( 0, nblocks * 4, 4 ):
        # ??? big endian?
        k1 = key[ block_start + 3 ] << 24 | \
             key[ block_start + 2 ] << 16 | \
             key[ block_start + 1 ] <<  8 | \
             key[ block_start + 0 ]
             
        k1 = c1 * k1 & 0xFFFFFFFF
        k1 = ( k1 << 15 | k1 >> 17 ) & 0xFFFFFFFF # inlined ROTL32
        k1 = ( c2 * k1 ) & 0xFFFFFFFF;
        
        h1 ^= k1
        h1  = ( h1 << 13 | h1 >> 19 ) & 0xFFFFFFFF # inlined _ROTL32 
        h1  = ( h1 * 5 + 0xe6546b64 ) & 0xFFFFFFFF

    # tail
    tail_index = nblocks * 4
    k1 = 0
    tail_size = length & 3

    if tail_size >= 3:
        k1 ^= key[ tail_index + 2 ] << 16
    if tail_size >= 2:
        k1 ^= key[ tail_index + 1 ] << 8
    if tail_size >= 1:
        k1 ^= key[ tail_index + 0 ]
    if tail_size != 0:
        k1  = ( k1 * c1 ) & 0xFFFFFFFF
        k1  = ( k1 << 15 | k1 >> 17 ) & 0xFFFFFFFF # _ROTL32
        k1  = ( k1 * c2 ) & 0xFFFFFFFF
        h1 ^= k1

    return fmix( h1 ^ length )

def gen_color(h = None, l = None, s = None):
    '''Generate a random nice color.'''
    if h is None:
        #Void solid red, green, blue
        h = random.uniform(0.02, 0.31) + random.choice([0, 1/3.0,2/3.0])
    else:
        h = h%(1/3.0)*0.29*3+0.02 + (int(h*3)/3.0)
    
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
    hashtext = bin(murmurhash(text, 22))[2:] #hash the text
    hashtext = hashtext.rjust(32, '0')
    size = (8,8)  #image size
    im = Image.new('RGB', size, 'white')
    draw = ImageDraw.Draw(im)

    #get the color of the icon
    h = int(hashtext[0:10], 2)/1023.0
    s = int(hashtext[11:21], 2)/1023.0
    l = int(hashtext[22:32], 2)/1023.0
    color = gen_color(h, l, s)
 
    for i in xrange(len(hashtext)): #32
        x, y = i%4, i/4
        posl = (x, y)
        posr = (7-x, y)
        if hashtext[i] == '1':
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
    im.save('icon_8_8.bmp')
    im.resize((128,128)).save('icon_128_128.bmp')