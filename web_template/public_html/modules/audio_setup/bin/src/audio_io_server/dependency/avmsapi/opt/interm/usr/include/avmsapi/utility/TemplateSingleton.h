/**
	@file	
	@brief	Singleton Template
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2017.03.27
	@see	http://vallista.tistory.com/entry/1-Singleton-Pattern-in-C
 */

#ifndef __TEMPLATE_SINGLETON_H__
#define __TEMPLATE_SINGLETON_H__

//#define DYNAMIC_SINGLETON

template < typename T >
class TemplateSingleton
{
protected:
	TemplateSingleton()
	{
	}
	virtual ~TemplateSingleton()
	{
	}

public:
	static T * instance()
	{
#ifdef DYNAMIC_SINGLETON
		if (_instance == NULL) {
			_instance = new T;
		}
		return _instance;		
#else	
		static T _instance;
		return &_instance;
#endif
	};

#ifdef DYNAMIC_SINGLETON
static void destroyInstance()
{
	if (_instance)
	{
		delete _instance;
		_instance = NULL;
	}
};
#endif

private:
#ifdef DYNAMIC_SINGLETON
	static T * _instance;
#endif
};

#ifdef DYNAMIC_SINGLETON
template <typename T> T * TemplateSingleton<T>::_instance = 0;
#endif
 
#if 0
///< Usage
class CObject : public TemplateSingleton<CObject>
{
public:
	CObject();
	~CObject();
};
#endif

#endif	// __TEMPLATE_SINGLETON_H__
